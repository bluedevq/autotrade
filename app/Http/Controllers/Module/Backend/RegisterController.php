<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Model\Entities\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

/**
 * Class RegisterController
 * @package App\Http\Controllers\Module\Backend
 */
class RegisterController extends BackendController
{
    const REGISTER_EMAIL = 'register_email';

    protected $_area = 'backend';

    public function __construct(User $user)
    {
        parent::__construct();
        $this->setModel($user);
    }

    public function index()
    {
        Session::forget(self::REGISTER_EMAIL);
        return $this->render();
    }

    public function valid()
    {
        // validate param
        $params = $this->getParams();
        $rules = $this->getModel()->rules();
        $messages = $this->getModel()->messages();
        $validator = Validator::make($params, $rules, $messages);
        if ($validator->fails()) {
            return $this->_backWithError($validator->errors()->first());
        }

        // validate email exist
        $user = $this->getModel()
            ->where('email', Arr::get($params, 'email'))
            ->first();
        if (!blank($user)) {
            return $this->_backWithError(new MessageBag(['email' => 'Email đã tồn tại']));
        }

        // save user into db
        $userData = [
            'email' => Arr::get($params, 'email'),
            'password' => Hash::make(Arr::get($params, 'password')),
            'name' => Arr::get($params, 'name'),
            'expired_date' => Carbon::now()->addDays(Common::getConfig('free_days_after_register'))->format('Y-m-d 23:59:59'),
            'role' => Common::getConfig('user_role.normal'),
            'status' => Common::getConfig('user_status.verify'),
        ];
        DB::beginTransaction();
        try {
            $entity = $this->getModel()->fill($userData);
            $entity->verify_token = $this->_generateToken($userData, Common::getConfig('user_status.verify'));
            $entity->verify_expired = Carbon::now()->addMinutes(Common::getConfig('verify_expired'));
            $entity->save();

            // send confirm token via email
            $data = [
                'name' => Arr::get($params, 'name'),
                'confirm_url' => route('backend.register.verify', [
                    'email' => $entity->email,
                    Common::getConfig('token_param_name') => $entity->verify_token,
                ])
            ];
            $this->_sendMail(Common::getConfig('mail.subject.register_verify'), Arr::get($params, 'email'), 'backend.mail.register_verify', $data);
            Session::put(self::REGISTER_EMAIL, $this->getParam('email'));

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $this->_backWithError(new MessageBag(['Lỗi hệ thống. Vui lòng thử lại sau.']));
        }

        return $this->_to('backend.register.success');
    }

    public function success()
    {
        $this->setViewData(['email' => Session::get(self::REGISTER_EMAIL)]);
        return $this->render();
    }

    public function verify()
    {
        $email = $this->getParam('email');
        $token = $this->getParam(Common::getConfig('token_param_name'));
        $user = $this->getModel()
            ->where('email', $email)
            ->first();
        $this->setViewData(['email' => $email]);

        // check user exist
        if (blank($user)) {
            $this->setViewData([
                'verify' => 'error',
                'verifyErrors' => 'Tài khoản <span class="text-info">' . $email . '</span> bạn muốn kích hoạt không tồn tại.',
            ]);
            return $this->render();
        }
        if ($user->status == Common::getConfig('user_status.active')) {
            $this->setViewData([
                'verify' => 'error',
                'verifyErrors' => 'Tài khoản <span class="text-info">' . $email . '</span> đã được kích hoạt.',
            ]);
            return $this->render();
        }
        // check verify token
        if ($user->verify_token != $token) {
            $this->setViewData([
                'verify' => 'error',
                'verifyErrors' => 'Liên kết xác thực tài khoản không đúng.',
            ]);
            return $this->render();
        }
        // check verify token expired
        if (Carbon::parse($user->verify_expired)->lt(Carbon::now())) {
            $this->setViewData([
                'verify' => 'error',
                'verifyErrors' => 'Liên kết xác thực tài khoản đã hết hạn.',
            ]);
            return $this->render();
        }

        // save user
        DB::beginTransaction();
        try {
            $user->verify_token = null;
            $user->verify_expired = null;
            $user->status = Common::getConfig('user_status.active');
            $user->save();

            // send mail success
            $dataMail = [
                'name' => $user->name,
                'url' => route('backend.login'),
                'app_name' => env('APP_NAME'),
            ];
            $this->_sendMail(Common::getConfig('mail.subject.register_verify_success'), $user->email, 'backend.mail.register_success', $dataMail);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            $this->setViewData([
                'verify' => 'error',
                'verifyErrors' => 'Xác thực tài khoản thất bại.',
            ]);
            return $this->render();
        }

        $this->setViewData(['verify' => 'success']);
        return $this->render();
    }
}
