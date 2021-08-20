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
        $breakItemPc = Common::getConfig('aresbo.method.break_item_pc');
        $breakItemSp = Common::getConfig('aresbo.method.break_item_mobile');
        $result = '';
        $spacePrefix = '&nbsp;';
        foreach ($signals as $index => $signal) {
            $signalText = Str::lower($signal) == 't' ? ('<span class="text-success-custom">' . Str::upper($signal) . '</span>') : ('<span class="text-danger-custom">' . Str::upper($signal) . '</span>');
            $result .= $signalText . $spacePrefix;
            if (!isMobile() && ($index + 1) % $breakItemPc == 0) {
                $result .= '<br/>';
            }
            if (isMobile() && ($index + 1) % $breakItemSp == 0) {
                $result .= '<br/>';
                continue;
            }
        }

        return $result;
    }

    public function getOrderPatternText()
    {
        $orderPatterns = explode(Common::getConfig('aresbo.order_pattern_delimiter'), $this->order_pattern);
        $breakItemPc = Common::getConfig('aresbo.method.break_item_pc');
        $breakItemSp = Common::getConfig('aresbo.method.break_item_mobile');
        $result = '';
        $spacePrefix = '&nbsp;';
        foreach ($orderPatterns as $index => $orderPattern) {
            $order = Str::substr($orderPattern, 0, 1);
            $amount = Str::substr($orderPattern, 1, Str::length($orderPattern) - 1);
            $bgLight = !blank($this->step) && $index === intval($this->step) ? ' bg-light ' : '';
            $textTmp = Str::lower($order) == 't' ? ('<span class="text-success-custom step-' . $index . $bgLight . '">' . Str::upper($order) . $amount . '</span>') : ('<span class="text-danger-custom step-' . $index . $bgLight . '">' . Str::upper($order) . $amount . '</span>');
            $result .= $textTmp . $spacePrefix;
            if (!isMobile() && ($index + 1) % $breakItemPc == 0) {
                $result .= '<br/>';
            }
            if (isMobile() && ($index + 1) % $breakItemSp == 0) {
                $result .= '<br/>';
                continue;
            }
        }

        return $result;
    }

    public function getProfitText()
    {
        if (blank($this->profit)) {
            return '';
        }
        return $this->profit > 0 ? ('<span class="text-success-custom">' . number_format($this->profit, 2) . '</span>') : ('<span class="text-danger-custom">' . number_format($this->profit, 2) . '</span>');
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
