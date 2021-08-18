<?php

namespace App\Model\Validators;

trait BotQueue
{
    public function rules()
    {
        return [
            'stop_loss' => 'nullable|regex:/^\-\d+(\.\d{1,2})?$/',
            'take_profit' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
        ];
    }

    public function messages()
    {
        return [
            'stop_loss.regex' => 'Vui lòng nhập cắt lỗ là số âm bé hơn 0.',
            'take_profit.regex' => 'Vui lòng nhập chốt lãi là số dương lớn hơn 0.',
        ];
    }
}
