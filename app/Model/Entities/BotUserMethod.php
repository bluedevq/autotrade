<?php

namespace App\Model\Entities;

use App\Helper\Common;
use App\Model\Base\BaseModel;
use Illuminate\Support\Str;

/**
 * Class BotUserMethod
 * @package App\Model\Entities
 */
class BotUserMethod extends BaseModel
{
    protected $table = 'bot_user_methods';

    protected $fillable = [
        'bot_method_default_id', 'bot_user_id', 'name', 'type', 'signal', 'order_pattern', 'stop_loss', 'stop_win', 'created_at', 'updated_at', 'deleted_at'
    ];

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
