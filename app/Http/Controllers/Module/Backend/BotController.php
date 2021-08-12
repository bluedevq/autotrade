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
            if (blank($botQueue)) {
                $botQueue = $this->fetchModel(BotQueue::class);
                $botQueue->user_id = backendGuard()->user()->id;
                $botQueue->bot_user_id = $botUserInfo->id;
                $botQueue->account_type = Common::getConfig('aresbo.account_demo');
                $botQueue->status = Common::getConfig('aresbo.bot_status.stop');
            }

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

            // reset method and bot queue profit when go to first time
            DB::beginTransaction();
            try {
                // reset method
                foreach ($listMethods as $method) {
                    $method->profit = null;
                    $method->step = null;
                    $method->save();
                }

                // reset bot queue
                $botQueue->profit = null;
                $botQueue->stop_loss = null;
                $botQueue->take_profit = null;
                $botQueue->save();

                // commit data
                DB::commit();
            } catch (\Exception $exception) {
                Log::error($exception);
                DB::rollBack();
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

            // get method order from database
            $methods = $this->fetchModel(BotUserMethod::class)->where('bot_user_id', $botQueue->bot_user_id)
                ->where('status', Common::getConfig('aresbo.method.active'))
                ->where(function ($q) {
                    $q->orWhere('deleted_at', '');
                    $q->orWhereNull('deleted_at');
                })->get();

            // research method to get bet order data
            $orderData = $this->_getOrderData($botQueue, $methods, $resultPrices);
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
                'bot_queue' => $botQueue,
                'list_open_orders' => $betResult,
                'order_data' => $orderData,
                'current_amount' => $currentAmount,
                'account_type' => $botQueue->account_type,
                'methods' => $methods,
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
                'step' => $entity->step,
                'profit' => $entity->getProfitText(),
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
        $responseData = [];

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
        $resultPrices = json_decode($this->getParam('list_prices'));
        if (blank($resultPrices)) {
            list($orderPrices, $resultPrices) = $this->_getListPrices();
        }
        $resultPrices = array_map(function ($item) {
            return (array)$item;
        }, $resultPrices);

        // get label
        foreach ($resultPrices as $index => $resultPrice) {
            $resultPrice = (array)$resultPrice;
            $responseData['label'][] = date('H:i d/m', Arr::get($resultPrice, 'open_order') / 1000);
        }

        // get data
        $listProfits = [];
        $totalVolume = 0;
        foreach ($methods as $method) {
            list($volume, $profit) = $this->_getProfitData($method, $resultPrices);
            $totalVolume += $volume;
            $listProfits[] = $profit;
            $responseData['datasets'][] = [
                'label' => $method->getNameText(),
                'data' => $profit,
                'fill' => false,
                'borderColor' => $method->getColorText(),
                'borderWidth' => Common::getConfig('aresbo.chart.chart_border_width'),
                'tension' => Common::getConfig('aresbo.chart.chart_tension'),
            ];
        }

        // set data for average
        $average = $this->_getAverageArray($listProfits);
        $responseData['datasets'][count($methods)] = [
            'label' => 'Tổng',
            'data' => $average,
            'fill' => false,
            'borderColor' => Common::getConfig('aresbo.chart.chart_total_color'),
            'borderWidth' => Common::getConfig('aresbo.chart.chart_total_border_width'),
            'tension' => Common::getConfig('aresbo.chart.chart_tension'),
        ];

        // shorten simulation data
        $this->_shortenSimulationData($responseData);

        // other configs
        $responseData['total_prices'] = count($resultPrices);
        $responseData['total_methods'] = count($methods);
        $responseData['total_volume'] = $totalVolume;
        $responseData['total_profit'] = $average[count($average) - 1];
        $responseData['highest_negative'] = min($average);
        $responseData['from'] = $responseData['label'][0];
        $responseData['to'] = $responseData['label'][count($responseData['label']) - 1];

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

    public function updateProfit()
    {
        $id = $this->getParam('id');
        $stopLoss = $this->getParam('stop_loss');
        $takeProfit = $this->getParam('take_profit');

        // validate
        $rules = $this->fetchModel(BotQueue::class)->rules();
        $messages = $this->fetchModel(BotQueue::class)->messages();
        $validator = Validator::make($this->getParams(), $rules, $messages);
        if ($validator->fails()) {
            $this->setData(['errors' => $validator->errors()->first()]);
            return $this->renderErrorJson();
        }

        // check bot user exist
        $botUser = $this->getModel()->where('email', Session::get(self::BOT_USER_EMAIL))->first();
        if (blank($botUser)) {
            return $this->_to('bot.clear_token');
        }

        DB::beginTransaction();
        try {
            $entity = $this->fetchModel(BotQueue::class)->where('id', $id)->first();
            $entity->stop_loss = $stopLoss;
            $entity->take_profit = $takeProfit;
            $entity->save();
            $this->setData([
                'success' => 'Lưu thành công.',
                'stop_loss' => $entity->getStopLoss(),
                'take_profit' => $entity->getTakeProfit(),
            ]);

            DB::commit();

            return $this->renderJson();
        } catch (\Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            $this->setData(['errors' => 'Đã xảy ra lỗi. Vui lòng thử lại.']);
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
        $patterns = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $method->order_pattern);
        $profitData = $methodProfit = [];
        $methodVolume = 0;

        foreach ($signals as $index => $signal) {
            $profitData[] = 0;
        }

        foreach ($candles as $index => $candle) {
            list($volume, $profit) = $this->_simulationBet($signals, $patterns, $method->type, $candles);
            $methodVolume += $volume;
            $position = $index + count($signals);
            $profitData[$position] = Arr::get($profitData, $position - 1) + $profit;
            unset($candles[$index]);
        }

        foreach ($profitData as $index => $item) {
            $methodProfit[] = $item;
        }

        return [$methodVolume, $methodProfit];
    }

    protected function _simulationBet($signals, $patterns, $type, $candles)
    {
        $profit = $volume = 0;
        $candles = array_values($candles);
        // check signal
        foreach ($signals as $index => $signal) {
            if (Str::lower($signal) != Str::lower(Arr::get($candles, $index . '.order_result'))) {
                return [$volume, $profit];
            }
        }
        // check pattern
        foreach ($patterns as $patternIndex => $pattern) {
            $orderType = Str::lower(Str::substr($pattern, 0, 1));
            $amount = Str::substr($pattern, 1);
            $volume += $amount;
            $win = $orderType == Str::lower(Arr::get($candles, (count($signals) + $patternIndex) . '.order_result'));
            if ($type == Common::getConfig('aresbo.method_type.value.martingale')) {
                if ($win) {
                    $profit += $amount * 0.95;
                    return [$volume, $profit];
                } else {
                    $profit += $amount * -1;
                }
            }
            if ($type == Common::getConfig('aresbo.method_type.value.paroli')) {
                if ($win) {
                    $profit += $amount * 0.95;
                } else {
                    $profit += $amount * -1;
                    return [$volume, $profit];
                }
            }
        }

        return [$volume, $profit];
    }

    protected function _shortenSimulationData(&$responseData)
    {
        $defaultSize = Common::getConfig('aresbo.chart.chart_default_step_size');
        $range = Common::getConfig('aresbo.chart.chart_step_size');
        $stepSize = intdiv(count($responseData['label']), $defaultSize) > $range ? intdiv(count($responseData['label']), $range) : $defaultSize;
        foreach ($responseData['label'] as $index => $item) {
            if ($index == 0 || ($index + 1) % $stepSize == 0 || $index == count($responseData['label']) - 1) {
                continue;
            }
            unset($responseData['label'][$index]);
        }
        $responseData['label'] = array_values($responseData['label']);

        foreach ($responseData['datasets'] as $datasetIndex => $dataset) {
            foreach ($dataset['data'] as $dataIndex => $item) {
                if ($dataIndex == 0 || ($dataIndex + 1) % $stepSize == 0 || $dataIndex == count($dataset['data']) - 1) {
                    continue;
                }
                unset($responseData['datasets'][$datasetIndex]['data'][$dataIndex]);
            }
            $responseData['datasets'][$datasetIndex]['data'] = array_values($responseData['datasets'][$datasetIndex]['data']);
        }
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
            $this->setData(['errors' => 'Lỗi người dùng. Vui lòng thử lại.']);
            return $this->renderErrorJson();
        }
        // check user exist
        $user = backendGuard()->user();
        if (blank($user)) {
            $this->setData(['errors' => 'Lỗi người dùng. Vui lòng thử lại.']);
            return $this->renderErrorJson();
        }

        DB::beginTransaction();
        try {
            // check user expired
            $userExpired = Carbon::parse($user->expired_date)->lessThan(Carbon::now());
            $botQueue = $this->fetchModel(BotQueue::class)->where('user_id', backendGuard()->user()->id)
                ->where('bot_user_id', $botUser->id)
                ->first();
            if (blank($botQueue)) {
                $botQueue = $this->fetchModel(BotQueue::class);
                $botQueue->user_id = backendGuard()->user()->id;
                $botQueue->bot_user_id = $botUser->id;
            }
            $botQueue->account_type = $this->getParam('account_type', Common::getConfig('aresbo.account_demo'));
            $botQueue->status = $stop ? Common::getConfig('aresbo.bot_status.stop') : Common::getConfig('aresbo.bot_status.start');

            // check user expired
            if ($userExpired) {
                $botQueue->status = Common::getConfig('aresbo.bot_status.stop');
            }
            $botQueue->save();
            DB::commit();
            // check user expired
            if ($userExpired) {
                $this->setData(['errors' => new MessageBag(['errors' => 'Tài khoản của bạn đã hết hạn. Vui lòng liên hệ admin để được hỗ trợ.'])]);
                return $this->renderErrorJson();
            }
            $this->setData(['success' => ($stop ? 'Dừng' : 'Chạy') . ' auto thành công.']);

            return $this->renderJson();
        } catch (\Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            $this->setData(['errors' => new MessageBag(['errors' => ($stop ? 'Dừng' : 'Chạy') . ' auto thất bại, vui lòng thử lại.'])]);
        }

        return $this->renderErrorJson();
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

    protected function _getOrderData($botQueue, $methods, $resultPrices)
    {
        $accountType = $botQueue->account_type;

        // research and order
        $result = [];
        $totalProfit = blank($botQueue->profit) ? 0 : $botQueue->profit;
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
                $amount = $this->_getBetPattern($method->order_pattern, 'amount', false, $method->step);
                $method->profit = $win ? ($method->profit + $amount * 0.95) : ($method->profit - $amount);
                $totalProfit += $win ? ($amount * 0.95) : (-1 * $amount);

                // check stop loss or take profit total
                if (($botQueue->stop_loss && $totalProfit - $amount < $botQueue->stop_loss) || ($botQueue->take_profit && $totalProfit > $botQueue->take_profit)) {
                    $botQueue->profit = $totalProfit;
                    $botQueue->status = Common::getConfig('aresbo.bot_status.stop');
                    $botQueue->save();
                    return [];
                }

                // Martingale: next step order after lose
                if ($method->type == Common::getConfig('aresbo.method_type.value.martingale')) {
                    if (!$win && isset($orderPatterns[$method->step + 1])) {
                        $orderTmp = $this->_getOrder($method, $accountType, $method->step + 1);
                        if (blank($orderTmp)) {
                            $method->step = null;
                        } else {
                            $result[] = $orderTmp;
                            $method->step = $method->step + 1;
                        }
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
                        $orderTmp = $this->_getOrder($method, $accountType, $method->step + 1);
                        if (blank($orderTmp)) {
                            $method->step = null;
                        } else {
                            $result[] = $orderTmp;
                            $method->step = $method->step + 1;
                        }
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

        // save total profit for bot queue
        $botQueue->profit = $totalProfit;
        $botQueue->save();

        return $result;
    }

    protected function _betProcess($orderData = [])
    {
        $betResult = [];
        if (blank($orderData)) {
            return $betResult;
        }
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
        $botQueue = Arr::get($params, 'bot_queue');
        $listOpenOrders = Arr::get($params, 'list_open_orders');
        $orderData = Arr::get($params, 'order_data');
        $methods = Arr::get($params, 'methods');

        $currentAmount = Arr::get($params, 'current_amount');
        $accountType = Arr::get($params, 'account_type');
        $result['current_amount'] = $accountType == Common::getConfig('aresbo.account_demo') ? Arr::get($currentAmount, 'demo_balance') : Arr::get($currentAmount, 'available_balance');
        $result['current_amount'] = number_format($result['current_amount'], 2);
        $result['bot_queue'] = $botQueue->getAttributes();

        // update open orders
        foreach ($listOpenOrders as $index => $openOrder) {
            $result['open_orders'][] = [
                'time' => $openOrder['time'],
                'amount' => $openOrder['amt'],
                'type' => $openOrder['type'],
                'method_name' => Arr::get($orderData, $index . '.method_name'),
                'method_id' => Arr::get($orderData, $index . '.method_id'),
                'step' => Arr::get($orderData, $index . '.step'),
            ];
        }

        // update list method
        foreach ($methods as $method) {
            $result['methods'][] = [
                'id' => $method->id,
                'profit' => $method->getProfitText()
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
        // check order pattern
        $orderPatterns = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $method->order_pattern);
        if ($method->type == Common::getConfig('aresbo.method_type.value.martingale') && !isset($orderPatterns[$step])) {
            return [];
        }

        // bet amount
        $amount = $this->_getBetPattern($method->order_pattern, 'amount', true, $step);

        // check profit
        if (($method->stop_loss && ($method->profit - $amount < $method->stop_loss)) ||
            ($method->take_profit && ($method->profit + $amount * 0.95 > $method->take_profit))) {
            return [];
        }

        return [
            'betAccountType' => Common::getConfig('aresbo.bet_account_type.' . $accountType),
            'betAmount' => $amount,
            'betType' => $this->_getBetPattern($method->order_pattern, 'type', true, $step),
            'method_name' => $method->name,
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

    protected function _randomColor($except = ['ff0000'])
    {
        $color = $this->_randomColorPart() . $this->_randomColorPart() . $this->_randomColorPart();
        if (in_array($color, $except)) {
            $color = $this->_randomColor($except);
        }

        return $color;
    }

    protected function _randomColorPart()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    protected function _getAverageArray($array)
    {
        $sum = [];
        foreach ($array as $item) {
            foreach ($item as $index => $value) {
                isset($sum[$index]) ? $sum[$index] += $value : $sum[$index] = $value;
            }
        }

        return $sum;
    }
}
