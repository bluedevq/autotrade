<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Http\Supports\ApiResponse;
use App\Model\Entities\BotQueue;
use App\Model\Entities\BotUser;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;

/**
 * Class BotController
 * @package App\Http\Controllers\Module\Backend
 */
class BotController extends BackendController
{
    use ApiResponse;

    const ACCESS_TOKEN = 'access_token';
    const REFRESH_TOKEN = 'refresh_token';
    const TWOFA_TOKEN = '2fa_token';
    const TWOFA_REQUIRED = '2fa_required';
    const BOT_USER_EMAIL = 'bot_user_email';
    const TOTAL_OPEN_ORDER = 'total_open_order';

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (Session::has(self::TWOFA_REQUIRED)) {
            $this->setViewData([
                'require2Fa' => Session::get(self::TWOFA_REQUIRED)
            ]);
        }
        if (Session::has(self::REFRESH_TOKEN)) {
            $userInfo = $this->_getUserInfo();
            if (blank($userInfo)) {
                Session::forget(self::REFRESH_TOKEN);
                return $this->_to('bot.index');
            }
            $botQueue = BotQueue::where('users_id', backendGuard()->user()->id)
                ->where('bot_users_id', $userInfo->id)
                ->first();
            $this->setViewData([
                'userInfo' => $userInfo,
                'botQueue' => $botQueue,
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
            'captcha' => Common::getConfig('aresbo.api_url.captcha_token'),
            'email' => $email,
            'password' => $password
        ];
        $response = $this->_getNewToken($loginData, true);

        // check status
        if (!Arr::get($response, 'ok')) {
            $errors = new MessageBag(['Email hoặc mật khẩu sai. Vui lòng thử lại.']);
            return $this->_to('bot.index')->withErrors($errors)->withInput(request()->all());
        }

        // save session email login
        Session::put(self::BOT_USER_EMAIL, $email);

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

        // change bot queue after login

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
        $response = $this->requestApi(Common::getConfig('aresbo.api_url.get_token2fa_url'), $loginData);

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
        Session::forget(self::BOT_USER_EMAIL);

        return $this->_to('bot.index');
    }

    public function startAuto()
    {
        return $this->_processAuto();
    }

    public function stopAuto()
    {
        return $this->_processAuto(true);
    }

    public function bet()
    {
        // check time to bet
        if (date('s') > 30) {
            return $this->renderErrorJson();
        }

        // check bot queue has running
        $user = BotUser::where('email', Session::get(self::BOT_USER_EMAIL))->first();
        if (blank($user)) {
            return $this->_to('bot.clear_token');
        }
        $botQueueModel = new BotQueue();
        $botQueue = $botQueueModel->where('users_id', backendGuard()->user()->id)
            ->where('bot_users_id', $user->id)
            ->first();
        if (blank($botQueue) || $botQueue->status = Common::getConfig('aresbo.bot_status.stop')) {
            return $this->renderErrorJson();
        }

        // get list closed orders
        $closedOrders = $this->_getListOrders([
            'betAccountType' => $botQueue->account_type,
            'size' => Session::get(self::TOTAL_OPEN_ORDER)
        ], false);

        // get new refresh token
        $newRefreshToken = $this->_getNewToken();
        // check status
        if (!Arr::get($newRefreshToken, 'ok')) {
            $this->setData(['url' => route('bot.clear_token')]);
            return $this->renderErrorJson();
        }
        // save token
        Session::put(self::ACCESS_TOKEN, Arr::get($newRefreshToken, 'd.access_token'));
        Session::put(self::REFRESH_TOKEN, Arr::get($newRefreshToken, 'd.refresh_token'));

        // research method to get bet order data
        $orderData = $this->_getOrderData($botQueue->account_type);

        // bet new order
        $betResult = $this->_betProcess($orderData);
        if ($betResult !== true) {
            return $betResult;
        }

        // get current balance after bet
        $currentAmount = $this->requestApi(Common::getConfig('aresbo.api_url.get_balance'), [], 'GET', ['Authorization' => 'Bearer ' . Session::get(self::REFRESH_TOKEN)], true);

        // get list open orders
        $openOrders = $this->_getListOrders([
            'betAccountType' => $botQueue->account_type,
            'size' => Session::get(self::TOTAL_OPEN_ORDER)
        ]);
        Session::put(self::TOTAL_OPEN_ORDER, count($openOrders));

        // save bet new order

        // mapping result
        $result = $this->_mapOpenOrders([
            'list_open_orders' => $openOrders,
            'list_closed_orders' => $closedOrders,
            'order_data' => $orderData,
            'current_amount' => $currentAmount,
            'account_type' => $botQueue->account_type
        ]);
        $this->setData($result);

        return $this->renderJson();
    }

    protected function _processAuto($stop = false)
    {
        $botUserModel = new BotUser();
        $user = $botUserModel->where('email', Session::get(self::BOT_USER_EMAIL))->first();
        if (blank($user)) {
            return $this->_to('bot.clear_token');
        }
        $botQueueModel = new BotQueue();
        $botQueueData = $botQueueModel->where('users_id', backendGuard()->user()->id)
            ->where('bot_users_id', $user->id)
            ->first();
        $now = Carbon::now();
        $botQueue = [
            'users_id' => backendGuard()->user()->id,
            'bot_users_id' => $user->id,
            'account_type' => request()->get('account_type', Common::getConfig('aresbo.account_demo')),
            'status' => $stop ? 0 : 1,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($botQueueData) {
            $botQueue['id'] = $botQueueData->id;
            unset($botQueue['created_at']);
        }

        DB::beginTransaction();
        try {
            $botQueueData ? $botQueueModel->where('id', $botQueueData->id)->update($botQueue) : $botQueueModel->insert($botQueue);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            $errors = new MessageBag([($stop ? 'Dừng' : 'Chạy') . ' auto thất bại, vui lòng thử lại.']);
            return $this->_to('bot.index')->withErrors($errors);
        }

        return $this->_to('bot.index');
    }

    protected function _getUserInfo()
    {
        // check profile in database
        $dbProfile = BotUser::where('email', Session::get(self::BOT_USER_EMAIL))->first();
        if ($dbProfile && Carbon::now()->diffInDays(Carbon::parse($dbProfile->updated_at)) == 0) {
            $tokens = [
                'id' => $dbProfile->id,
                'access_token' => Session::get(self::ACCESS_TOKEN),
                'refresh_token' => Session::get(self::REFRESH_TOKEN),
            ];
            BotUser::where('email', Session::get(self::BOT_USER_EMAIL))->update($tokens);
            return $dbProfile;
        }

        // begin transaction
        DB::beginTransaction();
        try {
            $refreshToken = Session::get(self::REFRESH_TOKEN);
            $headers = ['Authorization' => 'Bearer ' . $refreshToken];

            // get profile
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.get_profile'), [], 'GET', $headers, true);
            $profile = Arr::get($response, 'd');

            // get balance
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.get_balance'), [], 'GET', $headers, true);
            $profile += Arr::get($response, 'd');

            // get rank
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.get_overview'), [], 'GET', $headers, true);
            $profile['rank'] = Arr::get($response, 'd.rank');
            $profile['sponsor'] = Arr::get($response, 'd.sponsor');

            // save profile
            $now = Carbon::now();
            $dataProfile = [
                'email' => Arr::get($profile, 'e'),
                'first_name' => Arr::get($profile, 'fn'),
                'last_name' => Arr::get($profile, 'ln'),
                'nick_name' => Arr::get($profile, 'nn'),
                'reference_name' => Arr::get($profile, 'sponsor'),
                'rank' => Arr::get($profile, 'rank'),
                'access_token' => Session::get(self::ACCESS_TOKEN),
                'refresh_token' => Session::get(self::REFRESH_TOKEN),
                'demo_balance' => Arr::get($profile, 'demoBalance'),
                'available_balance' => Arr::get($profile, 'availableBalance'),
                'usdt_available_balance' => Arr::get($profile, 'usdtAvailableBalance'),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $botUser = new BotUser();
            if ($dbProfile && $dbProfile->id) {
                $dataProfile['id'] = $dbProfile->id;
                unset($dataProfile['created_at']);
                $botUser->exists = true;
                $botUser->fill($dataProfile)->save(['id' => $dbProfile->id]);
            } else {
                $botUser->fill($dataProfile)->save();
            }

            // update bot queue
            $botQueue = BotQueue::where('users_id', backendGuard()->user()->id)
                ->where('bot_users_id', $botUser->id)
                ->first();
            if ($botQueue) {
                $botQueue->status = 0;
                $botQueue->save();
            }

            DB::commit();

            return $botUser;
        } catch (\Exception $exception) {
            DB::rollBack();
            return [];
        }
    }

    protected function _getListOrders($params = [], $open = true)
    {
        $params['page'] = 1;
        if (!isset($params['size']) || !$params['size']) {
            $params['size'] = 20;
        }
        if (!isset($params['betAccountType'])) {
            $params['betAccountType'] = 1;
        }
        $params['betAccountType'] = Common::getConfig('aresbo.bet_account_type.' . $params['betAccountType']);
        $url = $open ? Common::getConfig('aresbo.api_url.open_order') : Common::getConfig('aresbo.api_url.close_order');
        $response = $this->requestApi($url, $params, 'GET', ['Authorization' => 'Bearer ' . Session::get(self::REFRESH_TOKEN)], true);
        // check status
        if (!Arr::get($response, 'ok')) {
            return $this->renderErrorJson(200, ['data' => ['url' => route('bot.clear_token')]]);
        }
        $result = [];
        $listOrders = Arr::get($response, 'd.c');
        foreach ($listOrders as $item) {
            $result[] = [
                'amount' => Arr::get($item, 'betAmount'),
                'win_amount' => Arr::get($item, 'winAmount') - Arr::get($item, 'betAmount'),
                'type' => Arr::get($item, 'betType'),
                'result' => Arr::get($item, 'result'),
                'time' => Arr::get($item, 'createdDatetime'),
            ];
        }

        return $result;
    }

    protected function _getNewToken($params = [], $isLogin = false)
    {
        $params['client_id'] = 'aresbo-web';
        $params['grant_type'] = $isLogin ? 'password' : 'refresh_token';
        if (!$isLogin) {
            $params['refresh_token'] = Session::get(self::REFRESH_TOKEN);
        }

        return $this->requestApi(Common::getConfig('aresbo.api_url.get_token_url'), $params);
    }

    protected function _betProcess($orderData = [])
    {
        // bet new order
        foreach ($orderData as $betData) {
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.bet'), $betData, 'POST', ['Authorization' => 'Bearer ' . Session::get(self::REFRESH_TOKEN)]);
            // check status
            if (!Arr::get($response, 'ok')) {
                $errorCode = Arr::get($response, 'd.err_code');
                if ($errorCode == 'insufficient_bet_balance') {
                    return $this->renderErrorJson();
                }
                return $this->renderErrorJson(200, ['data' => ['url' => route('bot.clear_token')]]);
            }
        }
        return true;
    }

    protected function _getOrderData($accountType)
    {
        return [
            [
                'betAccountType' => Common::getConfig('aresbo.bet_account_type.' . $accountType),
                'betAmount' => 10,
                'betType' => Arr::random(['UP', 'DOWN']),
                'method' => 'Ngẫu nhiên',
            ]
        ];
    }

    protected function _mapOpenOrders($params = [])
    {
        $listOpenOrders = Arr::get($params, 'list_open_orders');
        $listClosedOrders = Arr::get($params, 'list_closed_orders');
        $orderData = Arr::get($params, 'order_data');
        $orderData = array_reverse($orderData);

        $currentAmount = Arr::get($params, 'current_amount');
        $accountType = Arr::get($params, 'account_type');
        $result['current_amount'] = $accountType == Common::getConfig('aresbo.account_demo') ? Arr::get($currentAmount, 'd.demoBalance') : Arr::get($currentAmount, 'd.availableBalance');
        $result['current_amount'] = number_format($result['current_amount'], 2);

        $result['closed_orders'] = $listClosedOrders;

        foreach ($listOpenOrders as $index => $openOrder) {
            $result['open_orders'][] = [
                'time' => $openOrder['time'],
                'amount' => $openOrder['amount'],
                'type' => $openOrder['type'],
                'method' => Arr::get($orderData, $index . '.method'),
            ];
        }

        return $result;
    }

    protected function _saveUser()
    {
    }

    protected function _updateBotQueue($botUser)
    {
        // update bot queue
        $botQueue = BotQueue::where('users_id', backendGuard()->user()->id)
            ->where('bot_users_id', $botUser->id)
            ->first();
        if ($botQueue) {
            $botQueue->status = 0;
            $botQueue->save();
        }
    }
}