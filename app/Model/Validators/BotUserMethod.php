<?php

namespace App\Model\Validators;
trait BotUserMethod
{
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'type' => 'required|integer|in:1,2',
            'signal' => 'required',
            'order_pattern' => 'required',
            'stop_loss' => 'nullable|integer',
            'stop_win' => 'nullable|integer',
            'status' => 'required|integer|in:0,1',
        ];
    }
}
