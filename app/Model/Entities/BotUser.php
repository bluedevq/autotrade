<?php

namespace App\Model\Entities;

use App\Model\Base\BaseModel;

/**
 * Class BotUser
 * @package App\Model\Entities
 */
class BotUser extends BaseModel
{
    protected $table = 'bot_users';

    protected $fillable = [
        'email', 'first_name', 'last_name', 'nick_name', 'reference_name', 'rank', 'access_token', 'refresh_token',
        'demo_balance', 'available_balance', 'usdt_available_balance', 'created_at', 'updated_at', 'deleted_at'
    ];
}
