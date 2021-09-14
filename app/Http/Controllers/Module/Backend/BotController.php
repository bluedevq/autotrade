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

    /**
     * BotController constructor.
     * @param BotUser $botUser
     * @param BotQueue $botQueue
     * @param BotUserMethod $botUserMethod
     * @param BotMethodDefault $botMethodDefault
     * @param User $user
     */
    public function __construct(BotUser $botUser, BotQueue $botQueue, BotUserMethod $botUserMethod, BotMethodDefault $botMethodDefault, User $user)
    {
        parent::__construct();
        $this->setModel($botUser);
        $this->registModel($botQueue, $botUserMethod, $botMethodDefault, $user);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        if (Session::has($this->getSessionKey('refresh_token'))) {
            $userId = backendGuard()->user()->getAuthIdentifier();
            // get user info
            $botUserInfo = $this->_getUserInfo();
            if (blank($botUserInfo)) {
                return $this->_to('bot.clear_token');
            }

            // get bot queue
            $botQueue = $this->fetchModel(BotQueue::class)->where('user_id', $userId)
                ->where('bot_user_id', $botUserInfo->id)
                ->first();
            if (blank($botQueue)) {
                $botQueue = $this->fetchModel(BotQueue::class);
                $botQueue->user_id = $userId;
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
                    $method->step = null;
                    $method->profit = null;
                    $method->stop_loss = null;
                    $method->take_profit = null;
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

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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
            Session::put($this->getSessionKey('bot_user_email'), $email);

            // check 2fa
            $require2Fa = Arr::get($response, 'd.require2Fa');
            $verifyDevice = Arr::get($response, 'd.verify-device');
            if ($require2Fa || $verifyDevice) {
                Session::put($this->getSessionKey('2fa_token'), Arr::get($response, 'd.t'));
                Session::put($this->getSessionKey('verify_device'), $verifyDevice);
                $this->setData([
                    'require2fa' => $require2Fa,
                    'verifyDevice' => $verifyDevice,
                ]);
                return $this->renderJson();
            }

            // save token
            Session::put($this->getSessionKey('access_token'), Arr::get($response, 'd.access_token'));
            Session::put($this->getSessionKey('refresh_token'), Arr::get($response, 'd.refresh_token'));

            $this->setData(['url' => route('bot.index')]);
        } catch (\Exception $exception) {
            Log::error($exception);
            $this->setData(['errors' => 'Lỗi hệ thống. Vui lòng thử lại.']);
            return $this->renderErrorJson();
        }
        return $this->renderJson();
    }

    /**
     * @return RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loginWith2FA()
    {
        try {
            // get token from AresBO
            $loginData = [
                'client_id' => 'aresbo-web',
                'code' => $this->getParam('require_2fa') ? $this->getParam('code') : $this->getParam('td_code'),
                'td_code' => $this->getParam('td_code'),
                'td_p_code' => Session::get($this->getSessionKey('verify_device_token')),
                'token' => Session::get($this->getSessionKey('2fa_token'))
            ];
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.get_token2fa_url'), $loginData);

            // clear 2fa required
            Session::forget($this->getSessionKey('2fa_token'));

            // check status
            if (!Arr::get($response, 'ok')) {
                return $this->_to('bot.index')->withErrors(new MessageBag(['Đăng nhập thất bại, vui lòng thử lại.']));
            }

            // save token
            Session::put($this->getSessionKey('access_token'), Arr::get($response, 'd.access_token'));
            Session::put($this->getSessionKey('refresh_token'), Arr::get($response, 'd.refresh_token'));

            return $this->_to('bot.index');
        } catch (\Exception $exception) {
            Log::error($exception);
        }
        return $this->_to('bot.index')->withErrors(new MessageBag(['Đăng nhập thất bại, vui lòng thử lại.']));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestCode()
    {
        try {
            // get token from AresBO
            $loginData = [
                'captcha' => Common::getConfig('aresbo.api_url.captcha_token'),
                'clientId' => 'aresbo-web',
                'token' => Session::get($this->getSessionKey('2fa_token'))
            ];
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.request_code'), $loginData);
            // check status
            if (!Arr::get($response, 'ok')) {
                $this->setData(['errors' => 'Gửi mã thất bại, vui lòng thử lại.']);
                return $this->renderErrorJson();
            }

            // save token
            Session::put($this->getSessionKey('verify_device_token'), Arr::get($response, 'd.data'));

            return $this->renderJson();
        } catch (\Exception $exception) {
            Log::error($exception);
        }
        return $this->renderErrorJson();
    }

    /**
     * @return RedirectResponse
     */
    public function clearToken()
    {
        Session::forget($this->getSessionKey('access_token'));
        Session::forget($this->getSessionKey('refresh_token'));
        Session::forget($this->getSessionKey('bot_user_email'));

        return $this->_to('bot.index');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function startAuto()
    {
        return $this->_processAuto();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopAuto()
    {
        return $this->_processAuto(true);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateLastPrices()
    {
        try {
            // get price
            list($orderPrices, $resultPrices) = $this->_getListPrices();
            if (blank($resultPrices)) {
                return $this->renderErrorJson();
            }
            $this->setData(['prices' => $resultPrices]);

            return $this->renderJson();
        } catch (\Exception $exception) {
            Log::error($exception);
            return $this->renderErrorJson();
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse|RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function bet()
    {
        try {
            // check time to bet
            if (date('s') > 50) {
                return $this->renderErrorJson();
            }

            // check bot queue has running
            $botUser = $this->getModel()->where('email', Session::get($this->getSessionKey('bot_user_email')))->first();
            if (blank($botUser)) {
                return $this->_to('bot.clear_token');
            }
            $botQueue = $this->fetchModel(BotQueue::class)->where('user_id', backendGuard()->user()->getAuthIdentifier())
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
            if (blank($methods)) {
                return $this->renderErrorJson();
            }

            // get price
            list($orderPrices, $resultPrices) = $this->_getListPrices();
            if (blank($resultPrices)) {
                return $this->renderErrorJson();
            }

            // research method to get bet order data
            $orderData = $this->_getOrderData($botQueue, $methods, $resultPrices);
            if (blank($orderData)) {
                return $this->renderErrorJson();
            }

            // bet new order
            $betResult = $this->_betProcess($orderData);
            if (blank($betResult)) {
                return $this->renderErrorJson();
            }

            // get current balance after bet
            $currentAmount = $this->_getBalance();

            // get new refresh token after 30 minutes
            if (date('i') % 20 == 0) {
                $newRefreshToken = $this->_getToken();
                // check status
                if (!Arr::get($newRefreshToken, 'ok')) {
                    $this->setData(['url' => route('bot.clear_token')]);
                    return $this->renderErrorJson();
                }
                // save token
                $accessToken = Arr::get($newRefreshToken, 'd.access_token');
                $refreshToken = Arr::get($newRefreshToken, 'd.refresh_token');
                Session::put($this->getSessionKey('access_token'), $accessToken);
                Session::put($this->getSessionKey('refresh_token'), $refreshToken);
                $botUser->access_token = $accessToken;
                $botUser->refresh_token = $refreshToken;
                $botQueue->account_type == Common::getConfig('aresbo.account_demo') ? $botUser->demo_balance = Arr::get($currentAmount, 'demo_balance') : $botUser->available_balance = Arr::get($currentAmount, 'available_balance');
                $botUser->save();
            }

            // get reward histories
            $rewardInfo = $this->_getRewardInfo();

            // mapping result
            $result = $this->_mapOpenOrders([
                'bot_queue' => $botQueue,
                'list_open_orders' => $betResult,
                'order_data' => $orderData,
                'current_amount' => $currentAmount,
                'account_type' => $botQueue->account_type,
                'methods' => $methods,
                'rewardInfo' => $rewardInfo,
            ]);
            $this->setData($result);
            return $this->renderJson();
        } catch (\Exception $exception) {
            Log::error($exception);
            return $this->renderErrorJson();
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse|RedirectResponse
     */
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
        $botUser = $this->getModel()->where('email', Session::get($this->getSessionKey('bot_user_email')))->first();
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

    /**
     * @param array $params
     * @param bool $isLogin
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _getToken($params = [], $isLogin = false)
    {
        $params['client_id'] = 'aresbo-web';
        $params['grant_type'] = $isLogin ? 'password' : 'refresh_token';
        if (!$isLogin) {
            $params['refresh_token'] = Session::get($this->getSessionKey('refresh_token'));
        }

        return $this->requestApi(Common::getConfig('aresbo.api_url.get_token_url'), $params);
    }

    /**
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _getUserInfo()
    {
        // check profile in database
        $dbProfile = $this->getModel()->where('email', Session::get($this->getSessionKey('bot_user_email')))->first();

        // begin transaction
        DB::beginTransaction();
        try {
            $refreshToken = Session::get($this->getSessionKey('refresh_token'));
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
                'access_token' => Session::get($this->getSessionKey('access_token')),
                'refresh_token' => Session::get($this->getSessionKey('refresh_token')),
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

    /**
     * @param bool $stop
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _processAuto($stop = false)
    {
        // check bot user exist
        $botUser = $this->getModel()->where('email', Session::get($this->getSessionKey('bot_user_email')))->first();
        if (blank($botUser)) {
            $this->setData(['errors' => 'Lỗi người dùng. Vui lòng thử lại.']);
            return $this->renderErrorJson();
        }
        // check user exist
        /**
         * @var User $user
         */
        $user = backendGuard()->user();
        if (blank($user)) {
            $this->setData(['errors' => 'Lỗi người dùng. Vui lòng thử lại.']);
            return $this->renderErrorJson();
        }

        DB::beginTransaction();
        try {
            // check user expired
            $userExpired = Carbon::parse($user->expired_date)->lessThan(Carbon::now());
            $botQueue = $this->fetchModel(BotQueue::class)->where('user_id', $user->getAuthIdentifier())
                ->where('bot_user_id', $botUser->id)
                ->first();
            if (blank($botQueue)) {
                $botQueue = $this->fetchModel(BotQueue::class);
                $botQueue->user_id = $user->getAuthIdentifier();
                $botQueue->bot_user_id = $botUser->id;
            }
            $botQueue->account_type = $this->getParam('account_type', Common::getConfig('aresbo.account_demo'));
            $botQueue->status = $stop ? Common::getConfig('aresbo.bot_status.stop') : Common::getConfig('aresbo.bot_status.start');

            // check user expired
            if ($userExpired) {
                $botQueue->status = Common::getConfig('aresbo.bot_status.stop');
            }

            // update bot queue and list methods after stop auto
            if ($stop) {
                // update bot queue
                $botQueue->profit = null;
                $botQueue->stop_loss = null;
                $botQueue->take_profit = null;

                // update list methods
                $listMethods = $this->fetchModel(BotUserMethod::class)->where('bot_user_id', $botUser->id)
                    ->where(function ($q) {
                        $q->orWhere('deleted_at', '');
                        $q->orWhereNull('deleted_at');
                    })->get();
                foreach ($listMethods as $method) {
                    $method->step = null;
                    $method->profit = null;
                    $method->stop_loss = null;
                    $method->take_profit = null;
                    $method->save();
                }
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

    /**
     * @param $botQueue
     * @param $methods
     * @param $resultPrices
     * @return array
     */
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
                $lastOrder = (string)$this->_getBetPattern($method->order_pattern, 'type', false, $method->step);
                $win = Str::lower($lastOrder) == Str::lower(Arr::get($resultPrices, '0.order_result', ''));
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

    /**
     * @param array $orderData
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _betProcess($orderData = [])
    {
        $betResult = [];
        if (blank($orderData)) {
            return $betResult;
        }
        // bet new order
        foreach ($orderData as $betData) {
            if (blank($betData)) {
                return [];
            }
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.bet'), $betData, 'POST', ['Authorization' => 'Bearer ' . Session::get($this->getSessionKey('refresh_token'))]);
            // check status
            if (!Arr::get($response, 'ok')) {
                $errorCode = Arr::get($response, 'd.err_code');
                Log::error($errorCode);
                return [];
            }
            $betResult[] = Arr::get($response, 'd');
        }
        return $betResult;
    }

    /**
     * @param array $params
     * @return mixed
     */
    protected function _mapOpenOrders($params = [])
    {
        $botQueue = Arr::get($params, 'bot_queue');
        $listOpenOrders = Arr::get($params, 'list_open_orders');
        $orderData = Arr::get($params, 'order_data');
        $methods = Arr::get($params, 'methods');

        $currentAmount = Arr::get($params, 'current_amount');
        if ($currentAmount) {
            $accountType = Arr::get($params, 'account_type');
            $result['current_amount'] = $accountType == Common::getConfig('aresbo.account_demo') ? Arr::get($currentAmount, 'demo_balance') : Arr::get($currentAmount, 'available_balance');
            $result['current_amount'] = number_format($result['current_amount'], 2);
        }

        $result['bot_queue'] = $botQueue->getAttributes();

        // reward info
        $result['reward_info'] = Arr::get($params, 'rewardInfo');

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

    /**
     * @param $signals
     * @param $candles
     * @return bool
     */
    protected function _mapMethod($signals, $candles)
    {
        foreach ($signals as $index => $signal) {
            if (Str::lower($signal) != Str::lower(Arr::get($candles, $index . '.order_result'))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $method
     * @param $accountType
     * @param int $step
     * @return array
     */
    protected function _getOrder($method, $accountType, $step = 0)
    {
        // check order pattern
        $orderPatterns = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $method->order_pattern);
        if ($method->type == Common::getConfig('aresbo.method_type.value.martingale') && !isset($orderPatterns[$step])) {
            return [];
        }

        // bet amount
        $amount = $this->_getBetPattern($method->order_pattern, 'amount', true, $step);

        // check profit setting
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

    /**
     * @param $orderPattern
     * @param string $key
     * @param bool $convertType
     * @param int $step
     * @return mixed
     */
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

    /**
     * @param $botUserId
     * @return array
     */
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

    /**
     * @return array|\ArrayAccess|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _getRewardInfo()
    {
        $rewardInfo = [];
        // get reward after 10 minutes
        if (date('i') % 10 != 0) {
            return $rewardInfo;
        }

        try {
            $refreshToken = Session::get($this->getSessionKey('refresh_token'));
            $headers = ['Authorization' => 'Bearer ' . $refreshToken];

            // get total reward
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.reward_info'), [], 'GET', $headers, true);
            $rewardInfo = Arr::get($response, 'd');

            // get reward histories
            $rewardHistoriesData = [
                'page' => 1,
                'size' => 5,
                'total' => 1,
            ];
            $response = $this->requestApi(Common::getConfig('aresbo.api_url.reward_histories'), $rewardHistoriesData, 'GET', $headers, true);
            $rewardInfo['history'] = Arr::get($response, 'd.c.0');
        } catch (\Exception $exception) {
            Log::error($exception);
        }

        return $rewardInfo;
    }
}
