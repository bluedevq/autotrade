<?php

namespace App\Model\Presenters;

use App\Helper\Common;

trait PUser
{
    public function getName()
    {
        return $this->name;
    }

    public function getRole()
    {
        return Common::getConfig('user_role_text.' . $this->role);
    }

    public function getStatus()
    {
        return Common::getConfig('user_status_text.' . $this->status);
    }
}
