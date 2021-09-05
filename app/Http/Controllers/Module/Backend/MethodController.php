<?php

namespace App\Http\Controllers\Module\Backend;

use App\Helper\Common;
use App\Http\Supports\ApiResponse;
use App\Model\Entities\BotUser;
use App\Model\Entities\BotUserMethod;
use App\Model\Entities\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Class MethodController
 * @package App\Http\Controllers\Module\Backend
 */
class MethodController extends BackendController
{
    use ApiResponse;

    /**
     * BotController constructor.
     * @param BotUser $botUser
     * @param BotUserMethod $botUserMethod
     * @param User $user
     */
    public function __construct(BotUser $botUser, BotUserMethod $botUserMethod, User $user)
    {
        parent::__construct();
        $this->setModel($botUser);
        $this->registModel($botUserMethod, $user);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMethod()
    {
        return $this->renderJson();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function editMethod($id)
    {
        $entity = $this->_prepareFormMethod($id);
        $this->setData(['entity' => $entity]);
        return $this->renderJson();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
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
        $botUser = $this->getModel()->where('email', Session::get($this->getSessionKey('bot_user_email')))->first();
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
                $entity->status = Common::getConfig('aresbo.method.active');
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

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMethod()
    {
        // validate data
        $listMethodIds = request()->get('method_ids');
        $entities = $this->fetchModel(BotUserMethod::class)->whereIn('id', $listMethodIds)->get();
        if (blank($entities)) {
            $this->setData(['errors' => 'Phương pháp không tồn tại.']);
            return $this->renderErrorJson();
        }

        // delete data
        DB::beginTransaction();
        try {
            foreach ($entities as $entity) {
                $entity->delete();
            }
            $this->setData([
                'method_ids' => $this->getParam('method_ids'),
                'success' => 'Xóa phương pháp thành công.'
            ]);
            DB::commit();

            return $this->renderJson();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            $this->setData(['errors' => 'Lỗi hệ thống. Vui lòng thử lại.']);
        }

        return $this->renderErrorJson();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusMethod()
    {
        DB::beginTransaction();
        try {
            $entities = $this->fetchModel(BotUserMethod::class)->whereIn('id', $this->getParam('method_ids'))->get();
            foreach ($entities as $entity) {
                $entity->status = $this->getParam('status') == 'true' ? Common::getConfig('aresbo.method.active') : Common::getConfig('aresbo.method.stop');
                $entity->save();
            }
            $this->setData([
                'method_ids' => $this->getParam('method_ids'),
                'success' => ($this->getParam('status') ? 'Chạy' : 'Dừng') . ' phương pháp thành công.'
            ]);
            DB::commit();

            return $this->renderJson();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            $this->setData(['errors' => 'Lỗi hệ thống. Vui lòng thử lại.']);
        }

        return $this->renderErrorJson();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function research()
    {
        $responseData = [];
        $listProfits = [];
        $totalVolume = 0;
        // get bot user
        $user = $this->getModel()->where('email', Session::get($this->getSessionKey('bot_user_email')))->first();
        if (blank($user)) {
            $this->setData(['url' => route('bot.clear_token')]);
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
            $this->setData(['errors' => 'Không có phương pháp nào đang chạy.']);
            return $this->renderErrorJson();
        }
        // get price & candles
        $resultPrices = json_decode($this->getParam('list_prices', ''));
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
        // get datasets
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
        // shorten label
        $responseData['label'] = $this->_shortenData(Arr::get($responseData, 'label', []));
        // shorten datasets
        foreach ($responseData['datasets'] as $datasetIndex => $dataset) {
            $responseData['datasets'][$datasetIndex]['data'] = $this->_shortenData(Arr::get($dataset, 'data', []));
        }
        // other configs
        $responseData['total_prices'] = count($resultPrices);
        $responseData['total_methods'] = count($methods);
        $responseData['total_volume'] = $totalVolume;
        $responseData['total_profit'] = $average[count($average) - 1];
        $responseData['highest_negative'] = min($average);
        $responseData['from'] = Arr::get($responseData, 'label.0');
        $responseData['to'] = Arr::get($responseData, 'label.' . (count($responseData['label']) - 1));
        // set view data
        $this->setData($responseData);

        return $this->renderJson();
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
     * @param $method
     * @param $candles
     * @return array
     */
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

    /**
     * @param $signals
     * @param $patterns
     * @param $type
     * @param $candles
     * @return array
     */
    protected function _simulationBet($signals, $patterns, $type, $prices)
    {
        $profit = $volume = 0;
        $prices = array_values($prices);
        // check signal
        foreach ($signals as $index => $signal) {
            if (Str::lower($signal) != Str::lower(Arr::get($prices, $index . '.order_result'))) {
                return [$volume, $profit];
            }
        }
        // check pattern
        $martingaleType = Common::getConfig('aresbo.method_type.value.martingale');
        $paroliType = Common::getConfig('aresbo.method_type.value.paroli');
        foreach ($patterns as $patternIndex => $pattern) {
            $orderType = Str::lower(Str::substr($pattern, 0, 1));
            $amount = Str::substr($pattern, 1);
            $volume += ($amount == (int)$amount) ? (int)$amount : (float)$amount;
            $win = $orderType == Str::lower(Arr::get($prices, (count($signals) + $patternIndex) . '.order_result'));
            if ($type == $martingaleType) {
                if ($win) {
                    $profit += $amount * 0.95;
                    return [$volume, $profit];
                }
                $profit += $amount * -1;
            }
            if ($type == $paroliType) {
                if (!$win) {
                    $profit += $amount * -1;
                    return [$volume, $profit];
                }
                $profit += $amount * 0.95;
            }
        }

        return [$volume, $profit];
    }

    /**
     * @param array $array
     * @return array
     */
    protected function _shortenData($array = [])
    {
        $defaultSize = Common::getConfig('aresbo.chart.chart_default_step_size');
        $range = Common::getConfig('aresbo.chart.chart_step_size');
        $stepSize = intdiv(count($array), $defaultSize) > $range ? intdiv(count($array), $range) : $defaultSize;
        foreach ($array as $index => $item) {
            if ($index == 0 || ($index + 1) % $stepSize == 0 || $index == count($array) - 1) {
                continue;
            }
            unset($array[$index]);
        }
        return array_values($array);
    }

    /**
     * @param null $id
     * @return mixed|null
     */
    protected function _prepareFormMethod($id = null)
    {
        $entity = $this->fetchModel(BotUserMethod::class);
        if (!blank($id)) {
            $entity = $this->fetchModel(BotUserMethod::class)->where('id', $id)->first();
        }

        return $entity;
    }

    /**
     * @param $array
     * @return array
     */
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
