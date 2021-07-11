<?php

namespace App\Repository\Module\Backend;

use App\Model\User;

class UserRepository extends BackendRepository
{
    public function model()
    {
        return User::class;
    }
}
