<?php

namespace App\Model\Entities;

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
        'verify_token', 'verify_expired', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getExpiredDate()
    {
        return $this->expired_date;
    }
}
