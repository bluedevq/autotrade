<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Http\Supports\ApiResponse;
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

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        if (!Session::has($this->getSessionKey('refresh_token'))) {
            return $this->_to('bot.clear_token');
        }
        $balance = $this->_getBalance();
        if (blank($balance)) {
            return $this->_to('bot.clear_token');
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
            $response = $this->requestApi($url, $data, 'POST', ['Authorization' => 'Bearer ' . Session::get($this->getSessionKey('refresh_token'))]);
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
}
