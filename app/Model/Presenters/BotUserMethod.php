<?php

namespace App\Model\Presenters;

use App\Helper\Common;
use Illuminate\Support\Str;

trait BotUserMethod
{
    public function getNameText()
    {
        return $this->name;
    }

    public function getTypeText()
    {
        return Common::getConfig('aresbo.method_type.text.' . $this->type);
    }

    public function getSignalText()
    {
        $signals = explode(Common::getConfig('aresbo.order_signal_delimiter'), $this->signal);
        $text = [];
        foreach ($signals as $signal) {
            $text[] = Str::lower($signal) == 't' ? ('<span class="fw-bold text-success"> ' . Str::upper($signal) . '</span>') : ('<span class="fw-bold text-danger"> ' . Str::upper($signal) . '</span>');
        }
        return implode(Common::getConfig('aresbo.order_signal_delimiter'), $text);
    }

    public function getOrderPatternText()
    {
        $orderPatterns = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $this->order_pattern);
        $text = [];
        foreach ($orderPatterns as $index => $orderPattern) {
            $order = Str::substr($orderPattern, 0, 1);
            $amount = Str::substr($orderPattern, 1, Str::length($orderPattern) - 1);
            $textTmp = Str::lower($order) == 't' ? ('<span class="fw-bold text-success step-' . $index . '"> ' . Str::upper($order) . $amount . '</span>') : ('<span class="fw-bold text-danger step-' . $index . '"> ' . Str::upper($order) . $amount . '</span>');
            $text[] = $textTmp;
        }
        return implode(Common::getConfig('aresbo.order_pattern_delimiter'), $text);
    }

    public function getStopLossText()
    {
        return $this->stop_loss ? $this->stop_loss : '∞';
    }

    public function getTakeProfitText()
    {
        return $this->take_profit ? $this->take_profit : '∞';
    }

    public function getMethodStatusText()
    {
        return Common::getConfig('aresbo.method.text.' . $this->status);
    }

    public function getColorText()
    {
        return '#' . $this->color;
    }
}
