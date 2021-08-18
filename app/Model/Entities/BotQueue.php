<?php

namespace App\Model\Entities;

use App\Model\Base\BaseModel;

/**
 * Class BotQueue
 * @package App\Model\Entities
 */
class BotQueue extends BaseModel
{
    use \App\Model\Presenters\BotQueue;
    use \App\Model\Validators\BotQueue;

    protected $table = 'bot_queues';

    protected $fillable = [
        'user_id', 'bot_user_id', 'account_type', 'profit', 'stop_loss', 'take_profit', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];
}
