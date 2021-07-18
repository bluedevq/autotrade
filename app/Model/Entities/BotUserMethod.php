<?php

namespace App\Model\Entities;

use App\Helper\Common;
use App\Model\Base\BaseModel;

/**
 * Class BotUserMethod
 * @package App\Model\Entities
 */
class BotUserMethod extends BaseModel
{
    protected $table = 'bot_user_methods';

    protected $fillable = [
        'bot_method_default_id', 'name', 'type', 'signal', 'order_pattern', 'stop_loss', 'stop_win', 'created_at', 'updated_at', 'deleted_at'
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
        return $this->signal;
    }

    public function getOrderPatternText()
    {
        return $this->order_pattern;
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
