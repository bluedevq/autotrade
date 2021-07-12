<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;

/**
 * Class BotController
 * @package App\Http\Controllers\Module\Backend
 */
class BotController extends BackendController
{
    const ACCESS_TOKEN = 'access_token';
    const REFRESH_TOKEN = 'refresh_token';
    const PROFILE_INFO = 'profile_info';

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (Session::has(self::ACCESS_TOKEN)) {
            $userInfo = $this->_getUserInfo();
            if (blank($userInfo)) {
                Session::forget(self::ACCESS_TOKEN);
                return $this->_to(route('bot.index'));
            }
            $this->setViewData([
                'userInfo' => $userInfo
            ]);
        }
        return $this->render();
    }

    public function getToken()
    {
        // get data
        $email = request()->get('email');
        $password = request()->get('password');

        // validate
        if (blank($email) || blank($password)) {
            $errors = new MessageBag(['login_password' => ['Mật khẩu không đúng']]);
            return $this->_backWithError($errors);
        }

        // get token from AresBO
        $userData = [
            'captcha' => Common::getConfig('aresbo.captcha_token'),
            'client_id' => 'aresbo-web',
            'email' => $email,
            'grant_type' => 'password',
            'password' => $password
        ];
        $response = $this->requestApi(Common::getConfig('aresbo.get_token_url'), $userData);

        // save token
        Session::put(self::ACCESS_TOKEN, Arr::get($response, 'd.access_token'));
        Session::put(self::REFRESH_TOKEN, Arr::get($response, 'd.refresh_token'));

        return $this->_to(route('bot.index'));
    }

    public function clearToken()
    {
        Session::forget(self::ACCESS_TOKEN);
        Session::forget(self::REFRESH_TOKEN);
        Session::forget(self::PROFILE_INFO);

        return $this->_to(route('bot.index'));
    }

    protected function _getUserInfo()
    {
        if (Session::has(self::PROFILE_INFO)) {
            return Session::get(self::PROFILE_INFO);
        }
        try {
            $accessToken = Session::get(self::ACCESS_TOKEN);
            $headers = ['Authorization' => 'Bearer ' . $accessToken];

            // get profile
            $response = $this->requestApi(Common::getConfig('aresbo.get_profile'), [], 'GET', $headers, true);
            $profile = Arr::get($response, 'd');

            // get balance
            $response = $this->requestApi(Common::getConfig('aresbo.get_balance'), [], 'GET', $headers, true);
            $profile += Arr::get($response, 'd');
            Session::put(self::PROFILE_INFO, $profile);

            return $profile;
        } catch (\Exception $exception) {
            return [];
        }
    }
}
