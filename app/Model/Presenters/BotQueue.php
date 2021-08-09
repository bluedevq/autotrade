<?php

namespace App\Model\Presenters;

trait BotQueue
{
    public function getStopLoss()
    {
        return empty($this->stop_loss) ? '∞' : number_format($this->stop_loss, 2);
    }

    public function getTakeProfit()
    {
        return empty($this->take_profit) ? '∞' : number_format($this->take_profit, 2);
    }
}
