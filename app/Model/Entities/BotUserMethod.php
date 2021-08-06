<?php

namespace App\Model\Entities;

use App\Model\Base\BaseModel;

/**
 * Class BotUserMethod
 * @package App\Model\Entities
 */
class BotUserMethod extends BaseModel
{
    use \App\Model\Presenters\BotUserMethod;
    use \App\Model\Validators\BotUserMethod;

    protected $table = 'bot_user_methods';

    protected $fillable = [
        'id', 'bot_user_id', 'name', 'type', 'signal', 'order_pattern', 'step', 'profit', 'stop_loss', 'take_profit',
        'status', 'color', 'created_at', 'updated_at', 'deleted_at'
    ];
}
