<?php

namespace App\Model\Entities;

use App\Model\Base\BaseModel;

/**
 * Class BotQueue
 * @package App\Model\Entities
 */
class BotQueue extends BaseModel
{
    protected $table = 'bot_queues';

    protected $fillable = [
        'users_id', 'bot_users_id', 'account_type', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];
}
