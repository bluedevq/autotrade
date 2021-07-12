<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
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

    public function index()
    {
        return $this->render();
    }

    public function auth()
    {
        $data = request()->all();
        $userData = [
            'email' => Arr::get($data, 'email'),
            'password' => Arr::get($data, 'password')
        ];
        if (backendGuard()->attempt($userData)) {
            return $this->_redirectToHome();
        }
        $errors = new MessageBag(['login_password' => ['Password error']]);
        return $this->_backWithError($errors);
    }

    public function logout()
    {
        backendGuard()->logout();
        Session::flush();
        return $this->_redirectToHome();
    }
}
