<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Http\Supports\ApiResponse;
use App\Model\Entities\BotMethodDefault;
use App\Model\Entities\BotQueue;
use App\Model\Entities\BotUser;
use App\Model\Entities\BotUserMethod;
use App\Model\Entities\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
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

    public function __construct(BotUser $botUser, BotQueue $botQueue, BotUserMethod $botUserMethod, BotMethodDefault $botMethodDefault, User $user)
    {
        parent::__construct();
        $this->setModel($botUser);
        $this->registModel($botQueue, $botUserMethod, $botMethodDefault, $user);
    }

    public function index()
    {
        if (Session::has(self::REFRESH_TOKEN)) {
            // get user info
            $botUserInfo = $this->_getUserInfo();
            if (blank($botUserInfo)) {
                return $this->_to('bot.clear_token');
            }

            // get bot queue
            $botQueue = $this->fetchModel(BotQueue::class)->where('user_id', backendGuard()->user()->id)
                ->where('bot_user_id', $botUserInfo->id)
                ->first();

            // get list methods
            $listMethods = $this->fetchModel(BotUserMethod::class)->where('bot_user_id', $botUserInfo->id)
                ->where(function ($q) {
                    $q->orWhere('deleted_at', '');
                    $q->orWhereNull('deleted_at');
                })
                ->orderBy($this->getParam('sort_field', 'id'), $this->getParam('sort_type', 'asc'))
                ->get();
            if (blank($listMethods)) {
                $listMethods = $this->_getDefaultMethod($botUserInfo->id);
            }

            // get price & candles
            list($orderCandles, $resultCandles) = $this->_getListPrices();
            $resultCandles = array_reverse($resultCandles);

            $this->setViewData([
                'botUserInfo' => $botUserInfo,
                'botQueue' => $botQueue,
                'methods' => $listMethods,
                'resultCandles' => $resultCandles,
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
            $response = $this->_getToken($loginData, true);

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
            $this->setData(['errors' => 'Lỗi hệ thống. Vui lòng thử lại.']);
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
        try {
            // check time to bet
            if (date('s') > 30) {
                return $this->renderErrorJson();
            }

            // get new refresh token after 30 minutes
            if (date('i') % 20 == 0) {
                $newRefreshToken = $this->_getToken();
                // check status
                if (!Arr::get($newRefreshToken, 'ok')) {
                    $this->setData(['url' => route('bot.clear_token')]);
                    return $this->renderErrorJson();
                }
                // save token
                Session::put(self::ACCESS_TOKEN, Arr::get($newRefreshToken, 'd.access_token'));
                Session::put(self::REFRESH_TOKEN, Arr::get($newRefreshToken, 'd.refresh_token'));
            }

            // get price
            list($orderPrices, $resultPrices) = $this->_getListPrices();
            if (blank($resultPrices)) {
                return $this->renderErrorJson();
            }
            $this->setData(['prices' => $resultPrices]);

            // check bot queue has running
            $botUser = $this->getModel()->where('email', Session::get(self::BOT_USER_EMAIL))->first();
            if (blank($botUser)) {
                return $this->_to('bot.clear_token');
            }
            $botQueue = $this->fetchModel(BotQueue::class)->where('user_id', backendGuard()->user()->id)
                ->where('bot_user_id', $botUser->id)
                ->first();
            if (blank($botQueue) || $botQueue->status == Common::getConfig('aresbo.bot_status.stop')) {
                return $this->renderErrorJson();
            }

            // research method to get bet order data
            $orderData = $this->_getOrderData($botQueue, $resultPrices);
            if (!is_array($orderData)) {
                return $this->renderErrorJson();
            }

            // bet new order
            $betResult = $this->_betProcess($orderData);
            if ($betResult === false) {
                return $this->renderErrorJson();
            }

            // get current balance after bet
            $currentAmount = $this->_getBalance();

            // mapping result
            $result = $this->_mapOpenOrders([
                'list_open_orders' => $betResult,
                'order_data' => $orderData,
                'current_amount' => $currentAmount,
                'account_type' => $botQueue->account_type
            ]);
            $this->setData($result);
            return $this->renderJson();
        } catch (\Exception $exception) {
            Log::error($exception);
            return $this->renderErrorJson();
        }
    }

    public function createMethod()
    {
        $entity = $this->_prepareFormMethod();
        return $this->renderJson();
    }

    public function editMethod($id)
    {
        $entity = $this->_prepareFormMethod($id);
        $this->setData(['entity' => $entity]);
        return $this->renderJson();
    }

    public function validateMethod()
    {
        // validate data
        $params = $this->getParams();
        $params['signal'] = explode(Common::getConfig('aresbo.order_signal_delimiter'), $params['signal']);
        $params['order_pattern'] = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $params['order_pattern']);
        $this->fetchModel(BotUserMethod::class)->setParams($params);
        $rules = $this->fetchModel(BotUserMethod::class)->rules();
        $messages = $this->fetchModel(BotUserMethod::class)->messages();
        $validator = Validator::make($params, $rules, $messages);
        if ($validator->fails()) {
            $this->setData(['errors' => $validator->errors()->first()]);
            return $this->renderErrorJson();
        }

        // check bot user
        $botUser = $this->getModel()->where('email', Session::get(self::BOT_USER_EMAIL))->first();
        if (blank($botUser)) {
            $this->setData(['errors' => 'Lỗi người dùng. Vui lòng thử lại.']);
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
            $entity->bot_user_id = $botUser->id;
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
                    'win' => $entity->getTakeProfitText(),
                ],
                'status' => $entity->getMethodStatusText(),
                'url' => [
                    'edit' => route('bot_method.edit', $entity->id)
                ],
            ]);
            $this->setData(['success' => 'Lưu thành công.']);

            return $this->renderJson();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            $this->setData(['errors' => 'Lỗi hệ thống. Vui lòng thử lại.']);
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
            $responseData['label'][] = date('H:i', Arr::get($resultCandle, 'open_order') / 1000);
//            if ($index == 0 || ($index + 1) % 10 == 0 || $index == count($resultCandles)) {
//                $responseData['label'][] = date('H:i', Arr::get($resultCandle, 'open_order') / 1000);
//            }
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

    public function moveMoney()
    {
        $balance = $this->_getBalance();
        if ($balance instanceof RedirectResponse) {
            return $balance;
        }
        $this->setViewData(['balance' => $balance]);
        return $this->render();
    }

    public function moveMoneyValid()
    {
        try {
            $param = $this->getParams();
            $amount = Arr::get($param, 'amount');
            $type = Arr::get($param, 'type');
            $url = $type == Common::getConfig('aresbo.move_money_type.wallet_to_trade') ? Common::getConfig('aresbo.api_url.move_usdtbo') : Common::getConfig('aresbo.api_url.move_bousdt');
            if ($amount <= 0) {
                $this->setData(['errors' => 'Giá trị không hợp lệ.']);
                return $this->renderErrorJson();
            }
            $data = [
                'amount' => $amount,
                'confirmed' => true
            ];
            $response = $this->requestApi($url, $data, 'POST', ['Authorization' => 'Bearer ' . Session::get(self::REFRESH_TOKEN)]);
            if (!Arr::get($response, 'ok')) {
                $message = Arr::get($response, 'd.err_code') == 'err_invalid_balance' ? 'Số dư của bạn không đủ.' : '';
                $this->setData(['errors' => $message]);
                return $this->renderErrorJson();
            }
            $this->setData([
                'amount' => $amount,
                'success' => $type == Common::getConfig('aresbo.move_money_type.wallet_to_trade') ? 'Bạn đã chuyển thành công ' . $amount . ' USDT đến Tài khoản Thực' : 'Bạn đã chuyển thành công ' . $amount . ' USDT đến Ví USDT'
            ]);

            return $this->renderJson();
        } catch (\Exception $exception) {
            Log::error($exception);
            $this->setData(['errors' => 'Đã xảy ra lỗi. Vui lòng thử lại.']);
            if ($exception->getCode() == 401) {
                $this->setData(['url' => route('bot.clear_token')]);
            }
        }
        return $this->renderErrorJson();
    }

    protected function _getToken($params = [], $isLogin = false)
    {
        $params['client_id'] = 'aresbo-web';
        $params['grant_type'] = $isLogin ? 'password' : 'refresh_token';
        if (!$isLogin) {
            $params['refresh_token'] = Session::get(self::REFRESH_TOKEN);
        }

        return $this->requestApi(Common::getConfig('aresbo.api_url.get_token_url'), $params);
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
            $profile += $this->_getBalance();

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
                'demo_balance' => Arr::get($profile, 'demo_balance'),
                'available_balance' => Arr::get($profile, 'available_balance'),
                'usdt_available_balance' => Arr::get($profile, 'usdt_available_balance'),
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

    protected function _processAuto($stop = false)
    {
        // check bot user exist
        $botUser = $this->getModel()->where('email', Session::get(self::BOT_USER_EMAIL))->first();
        if (blank($botUser)) {
            return $this->_to('bot.clear_token');
        }
        // check user exist
        $user = $this->fetchModel(User::class)
            ->where('status', Common::getConfig('user_status.active'))
            ->where(function ($q) {
                $q->orWhere('deleted_at', '');
                $q->orWhereNull('deleted_at');
            })->first();
        if (blank($user)) {
            return $this->_to('bot.clear_token');
        }
        // check user expired
        $userExpired = Carbon::parse($user->expired_date)->lessThan(Carbon::now());
        $botQueueData = $this->fetchModel(BotQueue::class)->where('user_id', backendGuard()->user()->id)
            ->where('bot_user_id', $botUser->id)
            ->first();
        $now = Carbon::now();
        $botQueue = [
            'user_id' => backendGuard()->user()->id,
            'bot_user_id' => $botUser->id,
            'account_type' => $this->getParam('account_type', Common::getConfig('aresbo.account_demo')),
            'status' => $stop ? Common::getConfig('aresbo.bot_status.stop') : Common::getConfig('aresbo.bot_status.start'),
            'created_at' => $now,
            'updated_at' => $now,
        ];
        // check user expired
        if ($userExpired) {
            $botQueue['status'] = Common::getConfig('aresbo.bot_status.stop');
        }
        if ($botQueueData) {
            $botQueue['id'] = $botQueueData->id;
            unset($botQueue['created_at']);
        }

        DB::beginTransaction();
        try {
            $botQueueData ? $this->fetchModel(BotQueue::class)->where('id', $botQueueData->id)->update($botQueue) : $this->fetchModel(BotQueue::class)->insert($botQueue);
            DB::commit();
            // check user expired
            if ($userExpired) {
                return $this->_to('bot.index')->withErrors(new MessageBag(['Tài khoản của bạn đã hết hạn. Vui lòng liên hệ admin để được hỗ trợ.']));
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            $errors = new MessageBag([($stop ? 'Dừng' : 'Chạy') . ' auto thất bại, vui lòng thử lại.']);
            return $this->_to('bot.index')->withErrors($errors);
        }

        return $this->_to('bot.index');
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

    protected function _getOrderData($botQueue, $resultPrices)
    {
        // get method order from database
        $methods = $this->fetchModel(BotUserMethod::class)->where('bot_user_id', $botQueue->bot_user_id)
            ->where('status', Common::getConfig('aresbo.method.active'))
            ->where(function ($q) {
                $q->orWhere('deleted_at', '');
                $q->orWhereNull('deleted_at');
            })->get();

        $accountType = $botQueue->account_type;

        // research and order
        $result = [];
        foreach ($methods as $method) {
            // reverse signal
            $signals = explode(Common::getConfig('aresbo.order_signal_delimiter'), $method->signal);
            $signals = array_reverse($signals);

            // check step has used
            if (blank($method->step)) {
                // check method mapping with signal
                $method->step = null;
                if ($this->_mapMethod($signals, $resultPrices)) {
                    // get order data from method
                    $result[] = $this->_getOrder($method, $accountType);
                    $method->step = 0;
                }
            } else {
                // check win or loss
                $lastOrder = $this->_getBetPattern($method->order_pattern, 'type', false, $method->step);
                $win = Str::lower($lastOrder) == Str::lower($resultPrices[0]['order_result']);
                $orderPatterns = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $method->order_pattern);
                // Martingale: next step order after lose
                if ($method->type == Common::getConfig('aresbo.method_type.value.martingale')) {
                    if (!$win && isset($orderPatterns[$method->step + 1])) {
                        // cần kiểm tra step
                        $result[] = $this->_getOrder($method, $accountType, $method->step + 1);
                        $method->step = $method->step + 1;
                    } else {
                        $method->step = null;
                        if ($this->_mapMethod($signals, $resultPrices)) {
                            // get order data from method
                            $result[] = $this->_getOrder($method, $accountType);
                            $method->step = 0;
                        }
                    }
                }
                // Paroli: next step order after win
                if ($method->type == Common::getConfig('aresbo.method_type.value.paroli')) {
                    if ($win && isset($orderPatterns[$method->step + 1])) {
                        // cần kiểm tra step
                        $result[] = $this->_getOrder($method, $accountType, $method->step + 1);
                        $method->step = $method->step + 1;
                    } else {
                        $method->step = null;
                        if ($this->_mapMethod($signals, $resultPrices)) {
                            // get order data from method
                            $result[] = $this->_getOrder($method, $accountType);
                            $method->step = 0;
                        }
                    }
                }
            }
            // save step method
            $method->save();
        }

        return $result;
    }

    protected function _betProcess($orderData = [])
    {
        $betResult = [];
        // bet new order
        foreach ($orderData as $betData) {
            if (blank($betData)) {
                continue;
            }
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.bet'), $betData, 'POST', ['Authorization' => 'Bearer ' . Session::get(self::REFRESH_TOKEN)]);
            // check status
            if (!Arr::get($response, 'ok')) {
                $errorCode = Arr::get($response, 'd.err_code');
                Log::error($errorCode);
                return false;
            }
            $betResult[] = Arr::get($response, 'd');
        }
        return $betResult;
    }

    protected function _mapOpenOrders($params = [])
    {
        $listOpenOrders = Arr::get($params, 'list_open_orders');
        $orderData = Arr::get($params, 'order_data');

        $currentAmount = Arr::get($params, 'current_amount');
        $accountType = Arr::get($params, 'account_type');
        $result['current_amount'] = $accountType == Common::getConfig('aresbo.account_demo') ? Arr::get($currentAmount, 'demo_balance') : Arr::get($currentAmount, 'available_balance');
        $result['current_amount'] = number_format($result['current_amount'], 2);

        foreach ($listOpenOrders as $index => $openOrder) {
            $result['open_orders'][] = [
                'time' => $openOrder['time'],
                'amount' => $openOrder['amt'],
                'type' => $openOrder['type'],
                'method' => Arr::get($orderData, $index . '.method'),
                'method_id' => Arr::get($orderData, $index . '.method_id'),
                'step' => Arr::get($orderData, $index . '.step'),
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

    protected function _getOrder($method, $accountType, $step = 0)
    {
        $orderPatterns = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $method->order_pattern);
        if ($method->type == Common::getConfig('aresbo.method_type.value.martingale') && !isset($orderPatterns[$step])) {
            return [];
        }
        return [
            'betAccountType' => Common::getConfig('aresbo.bet_account_type.' . $accountType),
            'betAmount' => $this->_getBetPattern($method->order_pattern, 'amount', true, $step),
            'betType' => $this->_getBetPattern($method->order_pattern, 'type', true, $step),
            'method' => $method->name,
            'method_id' => $method->id,
            'step' => $step,
        ];
    }

    protected function _getBetPattern($orderPattern, $key = '', $convertType = true, $step = 0)
    {
        $orderPatterns = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $orderPattern);
        $orderPattern = isset($orderPatterns[$step]) ? $orderPatterns[$step] : $orderPatterns[0];
        $order = [
            'type' => $convertType ? Common::getConfig('aresbo.order_type_pattern.' . Str::lower(Str::substr($orderPattern, 0, 1))) : Str::lower(Str::substr($orderPattern, 0, 1)),
            'amount' => Str::substr($orderPattern, 1)
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

    protected function _getDefaultMethod($botUserId)
    {
        $userMethods = [];

        // get default method from database
        $methodDefaults = $this->fetchModel(BotMethodDefault::class)
            ->where('status', Common::getConfig('aresbo.method.active'))
            ->where(function ($q) {
                $q->orWhere('deleted_at', '');
                $q->orWhereNull('deleted_at');
            })->get();
        if (blank($methodDefaults)) {
            return $userMethods;
        }

        // save method for user
        $model = $this->fetchModel(BotUserMethod::class);
        DB::beginTransaction();
        try {
            foreach ($methodDefaults as $index => $method) {
                $methodData = $method->getAttributes();
                unset($methodData['id'], $methodData['created_at'], $methodData['updated_at']);
                $entity = clone $model->setRawAttributes($methodData);
                $entity->bot_user_id = $botUserId;
                $entity->save();
                $userMethods[] = $entity;
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
        }

        return $userMethods;
    }

    protected function _getBalance($format = false)
    {
        // get balance
        try {
            $refreshToken = Session::get(self::REFRESH_TOKEN);
            $headers = ['Authorization' => 'Bearer ' . $refreshToken];
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.get_balance'), [], 'GET', $headers, true);
            $demoBalance = Arr::get($response, 'd.demoBalance');
            $availableBalance = Arr::get($response, 'd.availableBalance');
            $usdtAvailableBalance = Arr::get($response, 'd.usdtAvailableBalance');
            $balance = [
                'demo_balance' => $demoBalance,
                'available_balance' => $availableBalance,
                'usdt_available_balance' => $usdtAvailableBalance,
            ];
            if ($format) {
                $balance = [
                    'demo_balance' => $demoBalance > 0 ? number_format($demoBalance, 2) : 0,
                    'available_balance' => $availableBalance > 0 ? number_format($availableBalance, 2) : 0,
                    'usdt_available_balance' => $usdtAvailableBalance > 0 ? number_format($usdtAvailableBalance, 2) : 0,
                ];
            }
        } catch (\Exception $exception) {
            Log::error($exception);
            return $this->_to('bot.clear_token');
        }

        return $balance;
    }

    protected function _randomColor()
    {
        return $this->_randomColorPart() . $this->_randomColorPart() . $this->_randomColorPart();
    }

    protected function _randomColorPart()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }
}
