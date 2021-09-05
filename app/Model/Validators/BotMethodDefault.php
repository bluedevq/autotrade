<?php

namespace App\Model\Validators;

trait BotMethodDefault
{
    public function rules()
    {
        $rules = [
            'name' => 'required|max:255',
            'type' => 'required|integer|in:1,2',
            'stop_loss' => 'nullable|regex:/^\-\d+(\.\d{1,2})?$/',
            'take_profit' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
        ];

        $params = $this->getParams();
        foreach ($params['signal'] as $index => $item) {
            $rules['signal.' . $index] = 'required|in:t,T,g,G';
        }
        foreach ($params['order_pattern'] as $index => $item) {
            $rules['order_pattern.' . $index] = ['required', 'regex:/^[tTgG]\d+\.\d+$|^[tTgG]\d+$/'];
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'name.required' => 'Vui lòng nhập tên phương pháp.',
            'name.max' => 'Vui lòng nhập tên không quá 255 ký tự.',
            'type.required' => 'Vui lòng chọn kiểu phương pháp.',
            'signal.required' => 'Vui lòng nhập tín hiệu nến.',
            'order_pattern.required' => 'Vui lòng nhập lệnh đặt.',
            'stop_loss.regex' => 'Vui lòng nhập cắt lỗ là số âm bé hơn 0.',
            'take_profit.regex' => 'Vui lòng nhập chốt lãi là số dương lớn hơn 0.',
        ];

        $params = $this->getParams();
        foreach ($params['signal'] as $index => $item) {
            $messages['signal.' . $index . '.required'] = 'Vui lòng nhập đúng định dạng tín hiệu nến.';
            $messages['signal.' . $index . '.in'] = 'Vui lòng nhập đúng định dạng tín hiệu nến.';
        }
        foreach ($params['order_pattern'] as $index => $item) {
            $messages['order_pattern.' . $index . '.required'] = 'Vui lòng nhập đúng định dạng lệnh đặt.';
            $messages['order_pattern.' . $index . '.regex'] = 'Vui lòng nhập đúng định dạng lệnh đặt.';
        }

        return $messages;
    }
}
