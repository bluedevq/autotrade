<?php

namespace App\Model\Validators;

trait User
{
    public function rules()
    {
        $rules = [
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|max:20',
            'name' => 'required|max:255',
            'phone' => 'nullable|integer',
            'sex' => 'nullable|integer|in:0,1',
            'address' => 'nullable'
        ];

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Vui lòng nhập đúng định dạng email.',
            'email.max' => 'Vui lòng nhập email không quá 255 ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có 8 ký tự trở lên.',
            'password.max' => 'Mật khẩu không được quá 20 ký tự.',
            'name.required' => 'Vui lòng nhập tên của bạn.',
            'name.max' => 'Vui lòng nhập tên không quá 255 ký tự.',
        ];

        return $messages;
    }
}
