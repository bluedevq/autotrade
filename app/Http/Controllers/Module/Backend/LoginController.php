<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Model\Entities\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;

/**
 * Class LoginController
 * @package App\Http\Controllers\Module\Backend
 */
class LoginController extends BackendController
{
    protected $_area = 'backend';

    public function __construct(User $user)
    {
        parent::__construct();
        $this->setModel($user);
    }

    public function index()
    {
        return $this->render();
    }

    public function auth()
    {
        // validate params
        $valid = $this->getModel()->validateLogin($this->getParams());
        if ($valid !== true) {
            return $this->_backWithError($valid);
        }
        $email = request()->get('email');
        $password = request()->get('password');
        // validate exist
        $user = $this->getModel()
            ->where('email', $email)
            ->where(function ($q) {
                $q->orWhere('deleted_at', '');
                $q->orWhereNull('deleted_at');
            })
            ->first();
        if (blank($user)) {
            return $this->_backWithError(new MessageBag(['email' => ['Tài khoản không tồn tại.']]));
        }
        if ($user->status != Common::getConfig('user_status.active')) {
            return $this->_backWithError(new MessageBag(['email' => ['Tài khoản chưa được kích hoạt.']]));
        }
        if (Carbon::parse($user->expired_date)->lessThan(Carbon::now())) {
            return $this->_backWithError(new MessageBag(['email' => ['Tài khoản của bạn đã hết hạn. Vui lòng liên hệ admin để được hỗ trợ.']]));
        }
        $userData = [
            'email' => $email,
            'password' => $password,
        ];
        if (backendGuard()->attempt($userData)) {
            Session::flash('success', ['Đăng nhập thành công.']);
            return $this->_redirectToHome();
        }
        return $this->_backWithError(new MessageBag(['login_password' => ['Sai mật khẩu. Vui lòng thử lại.']]));
    }

    public function logout()
    {
        backendGuard()->logout();
        Session::flush();
        return $this->_redirectToHome();
    }
}
