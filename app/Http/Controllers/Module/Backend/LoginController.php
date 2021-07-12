<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Input;
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
    }

    protected function _backWithError($errors)
    {
        return $this->_back()->withErrors($errors)->withInput(request()->all());
    }

    protected function _redirectToHome()
    {
        $url = request()->get('return_url', Common::buildDashBoardUrl());
        $url = empty($url) ? Common::buildDashBoardUrl() : $url;
        return $this->_to($url);
    }
}
