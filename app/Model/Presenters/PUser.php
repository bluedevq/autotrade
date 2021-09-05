<?php

namespace App\Model\Presenters;

use App\Helper\Common;
use Carbon\Carbon;

trait PUser
{
    public function getName()
    {
        return $this->name;
    }

    public function getExpiredDate()
    {
        $expiredDate = Carbon::parse($this->expired_date);
        if ($expiredDate->lessThan(Carbon::now())) {
            return '<span class="text-danger-custom">Đã hết hạn</span>';
        }
        return $expiredDate->format('H:i:s d/m/Y');
    }

    public function getBotUsers()
    {
        $result = [];
        foreach ($this->botUserQueues as $botQueue) {
            $botQueue->botUser ? $result[] = $botQueue->botUser->nick_name : null;
        }
        return implode('<br/>', $result);
    }

    public function getRoleText()
    {
        return Common::getConfig('user_role_text.' . $this->role);
    }

    public function getStatus()
    {
        return Common::getConfig('user_status_text.' . $this->status);
    }
}
