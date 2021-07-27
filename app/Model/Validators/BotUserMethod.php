<?php

namespace App\Model\Validators;

trait BotUserMethod
{
    public function rules()
    {
        $rules = [
            'name' => 'required|max:255',
            'type' => 'required|integer|in:1,2',
            'order_pattern' => 'required',
            'stop_loss' => 'nullable|integer',
            'stop_win' => 'nullable|integer',
            'status' => 'required|integer|in:0,1',
        ];

        $params = $this->getParams();
        foreach ($params['signal'] as $index => $item) {
            $rules['signal.' . $index] = 'required|in:t,T,g,G';
        }
        foreach ($params['order_pattern'] as $index => $item) {
            $rules['order_pattern.' . $index] = ['regex:/^[tTgG]\d+\.\d+$|^[tTgG]\d+$/'];
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
            'status.required' => 'Vui lòng chọn trạng thái phương pháp.',
        ];

        $params = $this->getParams();
        foreach ($params['signal'] as $index => $item) {
            $messages['signal.' . $index . '.in'] = 'Vui lòng nhập đúng định dạng tín hiệu nến.';
        }
        foreach ($params['order_pattern'] as $index => $item) {
            $messages['order_pattern.' . $index . '.regex'] = 'Vui lòng nhập đúng định dạng lệnh đặt.';
        }

        return $messages;
    }
}
