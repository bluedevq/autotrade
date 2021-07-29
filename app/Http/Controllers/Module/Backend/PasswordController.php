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
 * Class PasswordController
 * @package App\Http\Controllers\Module\Backend
 */
class PasswordController extends BackendController
{
    const FORGOT_PASSWORD_EMAIL = 'forgot_password_email';

    protected $_area = 'backend';

    public function __construct(User $user)
    {
        parent::__construct();
        $this->setModel($user);
    }

    public function index()
    {
        Session::forget(self::FORGOT_PASSWORD_EMAIL);
        return $this->render();
    }

    public function valid()
    {
        // validate param
        $email = $this->getParam('email');
        if (blank($email)) {
            return $this->_backWithError(new MessageBag(['email' => 'Vui lòng nhập email.']));
        }

        // validate email exist
        $user = $this->getModel()
            ->where('email', $email)
            ->first();
        if (blank($user)) {
            return $this->_backWithError(new MessageBag(['email' => 'Email không tồn tại.']));
        }
        if ($user->status != Common::getConfig('user_status.active')) {
            return $this->_backWithError(new MessageBag(['email' => 'Tài khoản chưa được kích hoạt.']));
        }

        // save user into db
        DB::beginTransaction();
        try {
            $user->forgot_password_token = $this->_generateToken($user->getAttributes(), Common::getConfig('user_status.forgot_password'));
            $user->forgot_password_expired = Carbon::now()->addMinutes(Common::getConfig('forgot_password_expired'));
            $user->save();

            // send confirm token via email
            $data = [
                'name' => $user->name,
                'confirm_url' => route('backend.password.forgot.verify', [
                    'email' => $user->email,
                    Common::getConfig('token_param_name') => $user->forgot_password_token,
                ])
            ];
            $this->_sendMail(Common::getConfig('mail.subject.reset_password_verify'), $user->email, 'backend.mail.reset_password_verify', $data);
            Session::put(self::FORGOT_PASSWORD_EMAIL, $this->getParam('email'));

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $this->_backWithError(new MessageBag(['Lỗi hệ thống. Vui lòng thử lại sau.']));
        }

        return $this->_to('backend.password.forgot.success');
    }

    public function success()
    {
        $this->setViewData(['email' => Session::get(self::FORGOT_PASSWORD_EMAIL)]);
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
                'verifyErrors' => 'Tài khoản <span class="text-info">' . $email . '</span> không tồn tại.',
            ]);
            return $this->render();
        }
        if ($user->status != Common::getConfig('user_status.active')) {
            $this->setViewData([
                'verify' => 'error',
                'verifyErrors' => 'Tài khoản <span class="text-info">' . $email . '</span> chưa được kích hoạt.',
            ]);
            return $this->render();
        }
        // check forgot_password_token
        if ($user->forgot_password_token != $token) {
            $this->setViewData([
                'verify' => 'error',
                'verifyErrors' => 'Liên kết lấy lại mật khẩu tài khoản không đúng.',
            ]);
            return $this->render();
        }
        // check forgot_password_expired
        if (Carbon::parse($user->forgot_password_expired)->lt(Carbon::now())) {
            $this->setViewData([
                'verify' => 'error',
                'verifyErrors' => 'Liên kết lấy lại mật khẩu tài khoản đã hết hạn.',
            ]);
            return $this->render();
        }
        $this->setViewData(['verify' => 'success',]);

        return $this->render();
    }

    public function validNew()
    {
        // validate param
        $params = $this->getParams();
        $rule = $this->getModel()->getRule('password');
        $message = $this->getModel()->getMessage('password');
        $validator = Validator::make($params, $rule, $message);
        if ($validator->fails()) {
            return $this->_backWithError($validator->errors()->first());
        }

        // validate email exist
        $email = $this->getParam('email');
        $user = $this->getModel()
            ->where('email', $email)
            ->first();
        if (blank($user)) {
            return $this->_backWithError(new MessageBag(['email' => 'Email không tồn tại.']));
        }
        if ($user->status != Common::getConfig('user_status.active')) {
            return $this->_backWithError(new MessageBag(['email' => 'Tài khoản chưa được kích hoạt.']));
        }

        // save user into db
        DB::beginTransaction();
        try {
            $user->password = Hash::make($this->getParam('password'));
            $user->save();

            // send email change password success
            $data = [
                'name' => $user->name,
                'url' => route('backend.login'),
                'app_name' => env('APP_NAME'),
            ];
            $this->_sendMail(Common::getConfig('mail.subject.reset_password_success'), $user->email, 'backend.mail.reset_password_success', $data);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return $this->_backWithError(new MessageBag(['Lỗi hệ thống. Vui lòng thử lại sau.']));
        }

        return $this->_to('backend.forgot.password.new.success');
    }

    public function newSuccess()
    {
        return $this->render();
    }
}
