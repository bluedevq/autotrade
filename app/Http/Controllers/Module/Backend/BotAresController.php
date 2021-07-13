<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;

/**
 * Class BotAresController
 * @package App\Http\Controllers\Module\Backend
 */
class BotAresController extends BackendController
{
    const ACCESS_TOKEN = 'access_token';
    const REFRESH_TOKEN = 'refresh_token';
    const PROFILE_INFO = 'profile_info';
    const TWOFA_TOKEN = '2fa_token';
    const TWOFA_REQUIRED = '2fa_required';

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (Session::has(self::TWOFA_REQUIRED)) {
            // clear 2fa required
            //Session::forget(self::TWOFA_REQUIRED);
            //Session::forget(self::TWOFA_TOKEN);

            $this->setViewData([
                'require2Fa' => Session::get(self::TWOFA_REQUIRED)
            ]);
        }
        if (Session::has(self::ACCESS_TOKEN)) {
            $userInfo = $this->_getUserInfo();
            if (blank($userInfo)) {
                Session::forget(self::ACCESS_TOKEN);
                return $this->_to('bot.index');
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
            $errors = new MessageBag(['Email hoặc mật khẩu sai. Vui lòng thử lại.']);
            return $this->_backWithError($errors);
        }

        // get token from AresBO
        $loginData = [
            'captcha' => Common::getConfig('aresbo.captcha_token'),
            'client_id' => 'aresbo-web',
            'email' => $email,
            'grant_type' => 'password',
            'password' => $password
        ];
        $response = $this->requestApi(Common::getConfig('aresbo.get_token_url'), $loginData);

        // check status
        if (!Arr::get($response, 'ok')) {
            $errors = new MessageBag(['Email hoặc mật khẩu sai. Vui lòng thử lại.']);
            return $this->_to('bot.index')->withErrors($errors)->withInput(request()->all());
        }

        // check 2fa
        $require2Fa = Arr::get($response, 'd.require2Fa');
        if ($require2Fa) {
            Session::put(self::TWOFA_REQUIRED, $require2Fa);
            Session::put(self::TWOFA_TOKEN, Arr::get($response, 'd.t'));
            return $this->_to('bot.index');
        }

        // save token
        Session::put(self::ACCESS_TOKEN, Arr::get($response, 'd.access_token'));
        Session::put(self::REFRESH_TOKEN, Arr::get($response, 'd.refresh_token'));

        return $this->_to('bot.index');
    }

    public function getToken2Fa()
    {
        // get token from AresBO
        $loginData = [
            'client_id' => 'aresbo-web',
            'code' => request()->get('code'),
            'td_code' => '',
            'td_p_code' => '',
            'token' => Session::get(self::TWOFA_TOKEN)
        ];
        $response = $this->requestApi(Common::getConfig('aresbo.get_token2fa_url'), $loginData);

        // clear 2fa required
        Session::forget(self::TWOFA_REQUIRED);
        Session::forget(self::TWOFA_TOKEN);

        // check status
        if (!Arr::get($response, 'ok')) {
            return $this->_to('bot.index');
        }

        // save token
        Session::put(self::ACCESS_TOKEN, Arr::get($response, 'd.access_token'));
        Session::put(self::REFRESH_TOKEN, Arr::get($response, 'd.refresh_token'));

        return $this->_to('bot.index');
    }

    public function clearToken()
    {
        Session::forget(self::ACCESS_TOKEN);
        Session::forget(self::REFRESH_TOKEN);
        Session::forget(self::PROFILE_INFO);

        return $this->_to('bot.index');
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

            // get rank
            $response = $this->requestApi(Common::getConfig('aresbo.get_overview'), [], 'GET', $headers, true);
            $profile['rank'] = Arr::get($response, 'd.rank');
            $profile['sponsor'] = Arr::get($response, 'd.sponsor');
            Session::put(self::PROFILE_INFO, $profile);

            return $profile;
        } catch (\Exception $exception) {
            return [];
        }
    }
}
