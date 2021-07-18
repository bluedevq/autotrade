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
    const CURRENT_AMOUNT = 'current_amount';
    const LAST_AMOUNT = 'last_amount';

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
            $startAmount = 0;
            if ($botQueue) {
                $botQueue->account_type == Common::getConfig('aresbo.account_demo') ? $userInfo->demo_balance : $userInfo->available_balance;
            }
            Session::put(self::CURRENT_AMOUNT, $startAmount);
            $this->setViewData([
                'userInfo' => $userInfo,
                'botQueue' => $botQueue,
                'startAmount' => $startAmount,
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
        if (date('s') > 30) {
            return $this->renderErrorJson(200, ['data' => ['url' => route('bot.index')]]);
        }
        $botUserModel = new BotUser();
        $user = $botUserModel->where('email', Session::get(self::BOT_USER_EMAIL))->first();
        if (blank($user)) {
            return $this->_to('bot.clear_token');
        }
        $botQueueModel = new BotQueue();
        $botQueue = $botQueueModel->where('users_id', backendGuard()->user()->id)
            ->where('bot_users_id', $user->id)
            ->first();

        // bet
        $refreshToken = Session::get(self::REFRESH_TOKEN);
        $headers = ['Authorization' => 'Bearer ' . $refreshToken];
        $betData = [
            'betAccountType' => Common::getConfig('aresbo.bet_account_type.' . $botQueue->account_type),
            'betAmount' => 10,
            'betType' => Arr::random(['UP', 'DOWN'])
        ];

        try {
            // get current amount
            $wallet = $this->requestApi(Common::getConfig('aresbo.get_balance'), [], 'GET', $headers, true);
            $wallet = Arr::get($wallet, 'd');
            $currentAmount = Arr::get($wallet, $botQueue->account_type == Common::getConfig('aresbo.account_demo') ? 'demoBalance' : 'availableBalance');

            // get last order
            $lastResult = (intval($currentAmount) - intval(Session::get(self::LAST_AMOUNT))) > 0 ? 1 : 0;

            // update last order status

            // bet new order
            $response = $this->requestApi(Common::getConfig('aresbo.bet'), $betData, 'POST', $headers);

            // check status
            if (!Arr::get($response, 'ok')) {
                return $this->renderErrorJson(200, ['data' => ['url' => route('bot.clear_token')]]);
            }

            // set last amount
            $lastAmount = $currentAmount - 10;
            Session::put(self::LAST_AMOUNT, $lastAmount);

            // save order

        } catch (\Exception $exception) {
            return $this->renderErrorJson(200, ['data' => ['url' => route('bot.index')]]);
        }
        $betOrder = [
            'bet_ss' => Arr::get($response, 'd.ss'),
            'time' => date('H:i'),
            'amount' => number_format(Arr::get($response, 'd.amt'), 2),
            'type' => Arr::get($response, 'd.type'),
            'bet_tk_amount' => Arr::get($response, 'd.tk_amount'),
            'method' => Arr::random(['Ngẫu nhiên']),
            'last_result' => $lastResult,
            'current_amount' => number_format($lastAmount, 2),
        ];


        return $this->renderJson(['data' => $betOrder]);
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
            $response = $this->requestApi(Common::getConfig('aresbo.get_profile'), [], 'GET', $headers, true);
            $profile = Arr::get($response, 'd');

            // get balance
            $response = $this->requestApi(Common::getConfig('aresbo.get_balance'), [], 'GET', $headers, true);
            $profile += Arr::get($response, 'd');

            // get rank
            $response = $this->requestApi(Common::getConfig('aresbo.get_overview'), [], 'GET', $headers, true);
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

    protected function _saveUser() {}

    protected function _updateBotQueue($botUser) {
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
