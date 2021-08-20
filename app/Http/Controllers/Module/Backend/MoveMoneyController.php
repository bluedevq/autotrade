<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Http\Supports\ApiResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Class MoveMoneyController
 * @package App\Http\Controllers\Module\Backend
 */
class MoveMoneyController extends BackendController
{
    use ApiResponse;

    const REFRESH_TOKEN = 'refresh_token';

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        $balance = $this->_getBalance();
        if (blank($balance)) {
            $this->_to('bot.clear_token');
        }
        $this->setViewData(['balance' => $balance]);
        return $this->render();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function valid()
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

    /**
     * @param bool $format
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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
                    'demo_balance' => blank($demoBalance) || $demoBalance <= 0 ? 0 : number_format($demoBalance, 2),
                    'available_balance' => blank($availableBalance) || $availableBalance <= 0 ? 0 : number_format($availableBalance, 2),
                    'usdt_available_balance' => blank($usdtAvailableBalance) || $usdtAvailableBalance <= 0 ? 0 : number_format($usdtAvailableBalance, 2),
                ];
            }
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }

        return $balance;
    }
}
