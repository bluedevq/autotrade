<?php

namespace App\Model\Entities;

use App\Model\Base\BaseModel;

/**
 * Class BotMethodDefault
 * @package App\Model\Entities
 */
class BotMethodDefault extends BaseModel
{
    protected $table = 'bot_method_defaults';

    protected $fillable = [
        'id', 'name', 'type', 'signal', 'order_pattern', 'stop_loss', 'stop_win', 'status', 'color', 'created_at', 'updated_at', 'deleted_at'
    ];
}
