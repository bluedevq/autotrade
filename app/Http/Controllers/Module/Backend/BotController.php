<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Http\Supports\ApiResponse;
use App\Model\Entities\BotQueue;
use App\Model\Entities\BotUser;
use App\Model\Entities\BotUserMethod;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;

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
    const BOT_USER_EMAIL = 'bot_user_email';
    const TOTAL_OPEN_ORDER = 'total_open_order';

    public function __construct(BotUser $botUser, BotQueue $botQueue, BotUserMethod $botUserMethod)
    {
        parent::__construct();
        $this->setModel($botUser);
        $this->registModel($botQueue, $botUserMethod);
    }

    public function index()
    {
        if (Session::has(self::REFRESH_TOKEN)) {
            $userInfo = $this->_getUserInfo();
            if (blank($userInfo)) {
                Session::forget(self::REFRESH_TOKEN);
                return $this->_to('bot.index');
            }
            $botQueue = $this->fetchModel(BotQueue::class)->where('user_id', backendGuard()->user()->id)
                ->where('bot_user_id', $userInfo->id)
                ->first();
            $methods = $this->fetchModel(BotUserMethod::class)->where('bot_user_id', $userInfo->id)
                ->where(function ($q) {
                    $q->orWhere('deleted_at', '');
                    $q->orWhereNull('deleted_at');
                })
                ->orderBy($this->getParam('sort_field', 'id'), $this->getParam('sort_type', 'asc'))
                ->get();

            $this->setViewData([
                'userInfo' => $userInfo,
                'botQueue' => $botQueue,
                'methods' => $methods,
            ]);
        }

        return $this->render();
    }

    public function login()
    {
        try {
            // get data
            $email = $this->getParam('email');
            $password = $this->getParam('password');

            // validate
            if (blank($email) || blank($password)) {
                $this->setData(['errors' => 'Vui lòng nhập email và mật khẩu.']);
                return $this->renderErrorJson();
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
                $this->setData(['errors' => 'Email hoặc mật khẩu sai. Vui lòng thử lại.']);
                return $this->renderErrorJson();
            }

            // save session email login
            Session::put(self::BOT_USER_EMAIL, $email);

            // check 2fa
            $require2Fa = Arr::get($response, 'd.require2Fa');
            if ($require2Fa) {
                Session::put(self::TWOFA_TOKEN, Arr::get($response, 'd.t'));
                $this->setData(['require2fa' => route('bot.loginWith2FA')]);
                return $this->renderJson();
            }

            // save token
            Session::put(self::ACCESS_TOKEN, Arr::get($response, 'd.access_token'));
            Session::put(self::REFRESH_TOKEN, Arr::get($response, 'd.refresh_token'));

            $this->setData(['url' => route('bot.index')]);
        } catch (\Exception $exception) {
            Log::error($exception);
            return $this->renderErrorJson();
        }
        return $this->renderJson();
    }

    public function loginWith2FA()
    {
        try {
            // get token from AresBO
            $loginData = [
                'client_id' => 'aresbo-web',
                'code' => $this->getParam('code'),
                'td_code' => '',
                'td_p_code' => '',
                'token' => Session::get(self::TWOFA_TOKEN)
            ];
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.get_token2fa_url'), $loginData);

            // clear 2fa required
            Session::forget(self::TWOFA_TOKEN);

            // check status
            if (!Arr::get($response, 'ok')) {
                return $this->_to('bot.index')->withErrors(new MessageBag(['Đăng nhập thất bại, vui lòng thử lại.']));
            }

            // save token
            Session::put(self::ACCESS_TOKEN, Arr::get($response, 'd.access_token'));
            Session::put(self::REFRESH_TOKEN, Arr::get($response, 'd.refresh_token'));

            return $this->_to('bot.index');
        } catch (\Exception $exception) {
            Log::error($exception);
        }
        return $this->_to('bot.index')->withErrors(new MessageBag(['Đăng nhập thất bại, vui lòng thử lại.']));
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
        $user = $this->getModel()->where('email', Session::get(self::BOT_USER_EMAIL))->first();
        if (blank($user)) {
            return $this->_to('bot.clear_token');
        }
        $botQueue = $this->fetchModel(BotQueue::class)->where('user_id', backendGuard()->user()->id)
            ->where('bot_user_id', $user->id)
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
        $orderData = $this->_getOrderData($botQueue);
        if (!is_array($orderData)) {
            return $orderData;
        }

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

    public function createMethod()
    {
        $entity = $this->_prepareFormMethod();
        return $this->renderJson();
    }

    public function editMethod($id)
    {
        $entity = $this->_prepareFormMethod($id);
        $this->setData($entity);
        return $this->renderJson();
    }

    public function validateMethod()
    {
        // validate data
        $validator = Validator::make($this->getParams(), $this->fetchModel(BotUserMethod::class)->rules());
        if ($validator->fails()) {
            return $this->renderErrorJson();
        }
        $userInfo = $this->_getUserInfo();
        if (blank($userInfo)) {
            return $this->renderErrorJson();
        }

        // save data
        DB::beginTransaction();
        try {
            $entity = $this->fetchModel(BotUserMethod::class)->fill($this->getParams());
            $isCreate = true;
            if ($entity->id) {
                $entity->exists = true;
                $isCreate = false;
            } else {
                $entity->color = $this->_randomColor();
            }
            $entity->bot_user_id = $userInfo->id;
            $entity->save();
            DB::commit();
            $this->setData([
                'create' => $isCreate ? 1 : 0,
                'id' => $entity->id,
                'name' => $entity->getNameText(),
                'type' => $entity->getTypeText(),
                'signal' => $entity->getSignalText(),
                'pattern' => $entity->getOrderPatternText(),
                'stop' => [
                    'loss' => $entity->getStopLossText(),
                    'win' => $entity->getStopWinText(),
                ],
                'status' => $entity->getMethodStatusText(),
                'url' => [
                    'edit' => route('bot_method.edit', $entity->id)
                ],
            ]);

            return $this->renderJson();
//            new MessageBag(['success' => 'Lưu thành công']);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
        }

        return $this->renderErrorJson();
    }

    public function deleteMethod()
    {
        // validate data
        $id = request()->get('id');
        $entity = $this->fetchModel(BotUserMethod::class)->where('id', $id)->first();
        if (blank($entity)) {
            return $this->renderErrorJson();
        }

        // delete data
        DB::beginTransaction();
        try {
            $entity->delete();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
        }

        return $this->renderJson();
    }

    public function research()
    {
        $responseData = $datasets = [];

        // get bot user
        $user = $this->getModel()->where('email', Session::get(self::BOT_USER_EMAIL))->first();
        if (blank($user)) {
            return $this->renderErrorJson();
        }

        // get method active from database
        $methods = $this->fetchModel(BotUserMethod::class)->where('bot_user_id', $user->id)
            ->where('status', Common::getConfig('aresbo.method.active'))
            ->where(function ($q) {
                $q->orWhere('deleted_at', '');
                $q->orWhereNull('deleted_at');
            })->get();
        if (blank($methods)) {
            return $this->renderErrorJson();
        }

        // get price & candles
        list($orderCandles, $resultCandles) = $this->_getListPrices();

        // get label
        $resultCandles = array_reverse($resultCandles);
        foreach ($resultCandles as $index => $resultCandle) {
            if ($index == 0 || ($index + 1) % 10 == 0 || $index == count($resultCandles)) {
                $responseData['label'][] = date('H:i', Arr::get($resultCandle, 'open_order') / 1000);
            }
        }

        // get data
        foreach ($methods as $method) {
            $datasets[] = [
                'label' => $method->getNameText(),
                'data' => $this->_getProfitData($method, $resultCandles),
                'fill' => false,
                'borderColor' => $method->getColorText(),
                'tension' => Common::getConfig('aresbo.chart_tension'),
            ];
        }
        $responseData['datasets'] = $datasets;
        $this->setData($responseData);

        return $this->renderJson();
    }

    protected function _getProfitData($method, $candles)
    {
        $signals = explode(Common::getConfig('aresbo.order_signal_delimiter'), $method->signal);
        $profitData = [];

        foreach ($candles as $index => $candle) {
            $profitData[$index] = Arr::get($profitData, $index - 1) + $this->_simulationBet($signals, $candles, $this->_getBetPattern($method->order_pattern, 'type', false), $this->_getBetPattern($method->order_pattern, 'amount'));
            unset($candles[$index]);
        }

        return $profitData;
    }

    protected function _simulationBet($signals, $candles, $orderType, $amount)
    {
        $candles = array_values($candles);
        foreach ($signals as $index => $signal) {
            if (Str::lower($signal) != Str::lower(Arr::get($candles, $index . '.order_result'))) {
                return false;
            }
        }
        $win = $orderType == Str::lower(Arr::get($candles, (count($signals) + 1) . '.order_result'));

        return $win ? $amount * 0.95 : $amount * -1;
    }

    protected function _randomColor()
    {
        return $this->_randomColorPart() . $this->_randomColorPart() . $this->_randomColorPart();
    }

    protected function _randomColorPart()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    protected function _processAuto($stop = false)
    {
        $user = $this->getModel()->where('email', Session::get(self::BOT_USER_EMAIL))->first();
        if (blank($user)) {
            return $this->_to('bot.clear_token');
        }
        $botQueueData = $this->fetchModel(BotQueue::class)->where('user_id', backendGuard()->user()->id)
            ->where('bot_user_id', $user->id)
            ->first();
        $now = Carbon::now();
        $botQueue = [
            'user_id' => backendGuard()->user()->id,
            'bot_user_id' => $user->id,
            'account_type' => $this->getParam('account_type', Common::getConfig('aresbo.account_demo')),
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
            $botQueueData ? $this->fetchModel(BotQueue::class)->where('id', $botQueueData->id)->update($botQueue) : $this->fetchModel(BotQueue::class)->insert($botQueue);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            $errors = new MessageBag([($stop ? 'Dừng' : 'Chạy') . ' auto thất bại, vui lòng thử lại.']);
            return $this->_to('bot.index')->withErrors($errors);
        }

        return $this->_to('bot.index');
    }

    protected function _getUserInfo()
    {
        // check profile in database
        $dbProfile = $this->getModel()->where('email', Session::get(self::BOT_USER_EMAIL))->first();

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
            if ($dbProfile && $dbProfile->id) {
                $dataProfile['id'] = $dbProfile->id;
                unset($dataProfile['created_at']);
                $this->getModel()->exists = true;
                $this->getModel()->fill($dataProfile)->save(['id' => $dbProfile->id]);
            } else {
                $this->getModel()->fill($dataProfile)->save();
            }

            DB::commit();

            return $this->getModel();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
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

    protected function _getOrderData($botQueue)
    {
        // get method order from database
        $methods = $this->fetchModel(BotUserMethod::class)->where('bot_user_id', $botQueue->bot_user_id)
            ->where('status', Common::getConfig('aresbo.method.active'))
            ->where(function ($q) {
                $q->orWhere('deleted_at', '');
                $q->orWhereNull('deleted_at');
            })->get();

        $accountType = $botQueue->account_type;

        // get price & candles
        list($orderCandles, $resultCandles) = $this->_getListPrices();

        // research and order
        $result = [];
        foreach ($methods as $method) {
            $signals = explode(Common::getConfig('aresbo.order_signal_delimiter'), $method->signal);
            $signals = array_reverse($signals);
            if ($this->_mapMethod($signals, $resultCandles)) {
                $orderTmp = $this->_getOrder($method, $accountType);
                blank($orderTmp) ? null : $result[] = $orderTmp;
            }
        }

        return $result;
    }

    protected function _getListPrices()
    {
        $orderCandles = $resultCandles = [];
        try {
            // get price & candles
            $prices = $this->requestApi(Common::getConfig('aresbo.api_url.get_prices'), [], 'GET', ['Authorization' => 'Bearer ' . Session::get(self::REFRESH_TOKEN)]);
            if (!Arr::get($prices, 'ok')) {
                return [$orderCandles, $resultCandles];
            }
            $listCandles = array_reverse(Arr::get($prices, 'd'));
            $candlesKey = [
                'open_order',
                'open_price',
                'high_price',
                'low_price',
                'close_price',
                'base_volume',
                'close_order',
                'xxx',
                'order_type', // 1: order, 0: result
                'session',
            ];
            foreach ($listCandles as $item) {
                $candleTmp = array_combine($candlesKey, $item);
                $orderResult = Arr::get($candleTmp, 'close_price') - Arr::get($candleTmp, 'open_price');
                $candleTmp['order_result'] = $orderResult > 0 ? Common::getConfig('aresbo.order_type_text.up') : Common::getConfig('aresbo.order_type_text.down');
                if (Arr::get($candleTmp, 'order_type') == 1) {
                    $orderCandles[] = $candleTmp;
                    continue;
                }
                $resultCandles[] = $candleTmp;
            }
        } catch (\Exception $exception) {
            Log::error($exception);
        }

        return [$orderCandles, $resultCandles];
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

    protected function _mapMethod($signals, $candles)
    {
        foreach ($signals as $index => $signal) {
            if (Str::lower($signal) != Str::lower(Arr::get($candles, $index . '.order_result'))) {
                return false;
            }
        }

        return true;
    }

    protected function _getOrder($method, $accountType)
    {
        return [
            'betAccountType' => Common::getConfig('aresbo.bet_account_type.' . $accountType),
            'betAmount' => $this->_getBetPattern($method->order_pattern, 'amount'),
            'betType' => $this->_getBetPattern($method->order_pattern, 'type'),
            'method' => $method->name,
        ];
    }

    protected function _getBetPattern($orderPattern, $key = '', $convertType = true)
    {
        $orderPatterns = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $orderPattern);
        $orderPattern = $orderPatterns[0];
        $order = [
            'type' => $convertType ? Common::getConfig('aresbo.order_type_pattern.' . Str::lower(Str::substr($orderPattern, 0, 1))) : Str::lower(Str::substr($orderPattern, 0, 1)),
            'amount' => Str::substr($orderPattern, 1, Str::length($orderPattern) - 1)
        ];

        return Arr::get($order, $key, $order);
    }

    protected function _prepareFormMethod($id = null)
    {
        $entity = $this->fetchModel(BotUserMethod::class);
        if ($id) {
            $entity = $this->fetchModel(BotUserMethod::class)->where('id', $id)->first();
        }

        return $entity;
    }
}
