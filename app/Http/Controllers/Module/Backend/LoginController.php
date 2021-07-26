<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Model\Entities\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Input;
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
        $data = request()->all();
        $email = Arr::get($data, 'email');
        $password = Arr::get($data, 'password');
        // validate email
        if (blank($email)) {
            return $this->_backWithError(new MessageBag(['login_email' => ['Vui lòng nhập email.']]));
        }
        // validate password
        if (blank($password)) {
            return $this->_backWithError(new MessageBag(['login_password' => ['Vui lòng nhập mật khẩu.']]));
        }
        // validate exist
        $user = $this->getModel()
            ->where('email', $email)
            ->where(function ($q) {
                $q->orWhere('deleted_at', '');
                $q->orWhereNull('deleted_at');
            })
            ->first();
        if (blank($user)) {
            return $this->_backWithError(new MessageBag(['login_password' => ['Tài khoản không tồn tại.']]));
        }
        $userData = [
            'email' => Arr::get($data, 'email'),
            'password' => Arr::get($data, 'password')
        ];
        if (backendGuard()->attempt($userData)) {
            Session::flash('success', ['Đăng nhập thành công.']);
            return $this->_redirectToHome();
        }
        $errors = new MessageBag(['login_password' => ['Sai mật khẩu. Vui lòng thử lại.']]);
        return $this->_backWithError($errors);
    }

    public function logout()
    {
        backendGuard()->logout();
        Session::flush();
        return $this->_redirectToHome();
    }
}
