<?php

namespace App\Model\Entities;

use App\Helper\Common;
use App\Model\Base\Auth\UserAuthenticate;
use App\Model\Presenters\PUser;

/**
 * Class User
 * @package App\Model\Entities
 */
class User extends UserAuthenticate
{
    use PUser;
    use \App\Model\Validators\User;

    protected $table = 'users';

    protected $fillable = [
        'id', 'email', 'password', 'name', 'phone', 'sex', 'address', 'expired_date', 'forgot_password_token', 'forgot_password_expired',
        'verify_token', 'verify_expired', 'parent_id', 'role', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function botUserQueues()
    {
        return $this->hasMany(BotQueue::class, 'user_id', 'id');
    }

    public function getList(array $params = [], array $columns = [])
    {
        return $this->search($params, $columns)->with(['botUserQueues.botUser'])->paginate(Common::getConfig('pagination.' . $this->getTable(), Common::getConfig('pagination.default')));
    }
}
