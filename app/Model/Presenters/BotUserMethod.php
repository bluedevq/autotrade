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
            $text[] = $signal == 'T' ? ('<span class="fw-bold text-success"> ' . $signal . '</span>') : ('<span class="fw-bold text-danger"> ' . $signal . '</span>');
        }
        return implode(Common::getConfig('aresbo.order_signal_delimiter'), $text);
    }

    public function getOrderPatternText()
    {
        $orderPatterns = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $this->order_pattern);
        $text = [];
        foreach ($orderPatterns as $orderPattern) {
            $order = Str::substr($orderPattern, 0, 1);
            $amount = Str::substr($orderPattern, 1, Str::length($orderPattern) - 1);
            $textTmp = $order == 'T' ? ('<span class="fw-bold text-success"> ' . $order . $amount . '</span>') : ('<span class="fw-bold text-danger"> ' . $order . $amount . '</span>');
            $text[] = $textTmp;
        }
        return implode(Common::getConfig('aresbo.order_pattern_delimiter'), $text);
    }

    public function getStopLossText()
    {
        return $this->stop_loss ? $this->stop_loss : '∞';
    }

    public function getStopWinText()
    {
        return $this->stop_win ? $this->stop_win : '∞';
    }

    public function getMethodText()
    {
        return Common::getConfig('aresbo.method.text.' . $this->status);
    }
}
