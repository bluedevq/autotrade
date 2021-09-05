<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\Traits\CRUD;
use App\Http\Controllers\Base\Traits\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Class BackendController
 * @package App\Http\Controllers\Module\Backend
 */
class BackendController extends BaseController
{
    use CRUD, Model;

    protected $_area = 'backend';

    protected $_sessionKeys = [
        'access_token' => 'access_token',
        'refresh_token' => 'refresh_token',
        '2fa_token' => '2fa_token',
        'verify_device' => 'verify_device',
        'verify_device_token' => 'verify_device_token',
        'bot_user_email' => 'bot_user_email',
    ];

    public function getSessionKey($key)
    {
        return Arr::get($this->_sessionKeys, $key);
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->setEntities($this->getModel()->getList($this->getParams()));
        return $this->render();
    }

    public function create()
    {
        $this->_prepareForm();
        return $this->render();
    }

    public function edit($id)
    {
        $this->_prepareForm($id);
        return $this->render();
    }

    public function render($view = null, $params = array(), $mergeData = array())
    {
        $this->setTitle(Common::getConfig('bot_title'));
        return parent::render($view, $params, $mergeData);
    }

    protected function _prepareForm($id = null)
    {
    }

    protected function _sendMail($subject, $to, $view, $data = [])
    {
        $from = Common::getConfig('mail.from');
        $sender = Common::getConfig('mail.sender');
        $content = '';
        $contentHtml = view($view, compact('data'));
        return $this->getMailer()->sendmail($from, $sender, $to, $subject, $content, [], $contentHtml);
    }

    protected function _generateToken($data, $type)
    {
        if ($type == Common::getConfig('user_status.verify')) {
            return hash('sha256', Common::getConfig('hash.verify.password') . Common::getConfig('hash.delimiter') . json_encode($data));
        }
        if ($type == Common::getConfig('user_status.forgot_password')) {
            return hash('sha256', Common::getConfig('hash.forgot_password.password') . Common::getConfig('hash.delimiter') . json_encode($data));
        }
        return hash('sha256', Common::getConfig('hash.default.password') . Common::getConfig('hash.delimiter') . json_encode($data));
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _getListPrices()
    {
        $orderCandles = $resultCandles = [];
        $orderPriceType = Common::getConfig('aresbo.order_type.oder');
        $priceUp = Common::getConfig('aresbo.order_type_text.up');
        $priceDown = Common::getConfig('aresbo.order_type_text.down');
        try {
            // get list prices
            $prices = $this->requestApi(Common::getConfig('aresbo.api_url.get_prices'), [], 'GET', ['Authorization' => 'Bearer ' . Session::get($this->getSessionKey('refresh_token'))]);
            if (!Arr::get($prices, 'ok')) {
                return [$orderCandles, $resultCandles];
            }
            $listCandles = array_reverse(Arr::get($prices, 'd', []));
            // get price keys
            $priceKeys = Common::getConfig('aresbo.price_keys');
            // get list order prices, result prices
            foreach ($listCandles as $item) {
                $priceTmp = array_combine($priceKeys, $item);
                $orderResult = Arr::get($priceTmp, 'close_price') - Arr::get($priceTmp, 'open_price');
                $priceTmp['order_result'] = $orderResult > 0 ? $priceUp : $priceDown;
                Arr::get($priceTmp, 'order_type') == $orderPriceType ? $orderCandles[] = $priceTmp : $resultCandles[] = $priceTmp;
            }
        } catch (\Exception $exception) {
            Log::error($exception);
        }

        return [$orderCandles, $resultCandles];
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
            $refreshToken = Session::get($this->getSessionKey('refresh_token'));
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

    /**
     * @param array $except
     * @return string
     */
    protected function _randomColor($except = ['ff0000'])
    {
        $color = $this->_randomColorPart() . $this->_randomColorPart() . $this->_randomColorPart();
        if (in_array($color, $except)) {
            $color = $this->_randomColor($except);
        }

        return $color;
    }

    /**
     * @return string
     */
    protected function _randomColorPart()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }
}
