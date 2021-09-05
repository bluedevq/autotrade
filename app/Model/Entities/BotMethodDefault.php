<?php

namespace App\Model\Entities;

use App\Model\Base\BaseModel;
use App\Model\Presenters\PBotMethodDefault;

/**
 * Class BotMethodDefault
 * @package App\Model\Entities
 */
class BotMethodDefault extends BaseModel
{
    use PBotMethodDefault;
    use \App\Model\Validators\BotMethodDefault;

    protected $table = 'bot_method_defaults';

    protected $fillable = [
        'id', 'name', 'type', 'signal', 'order_pattern', 'stop_loss', 'take_profit', 'status', 'color', 'created_at', 'updated_at', 'deleted_at'
    ];
}
