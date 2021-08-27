<?php

namespace App\Model\Validators;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

trait User
{
    protected function _getRuleDefaults()
    {
        return [
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|max:20',
            'name' => 'required|max:255',
            'phone' => 'nullable|integer',
            'sex' => 'nullable|integer|in:0,1',
            'address' => 'nullable'
        ];
    }

    protected function _getMessageDefaults()
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Vui lòng nhập đúng định dạng email.',
            'email.max' => 'Vui lòng nhập email không quá 255 ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có 8 ký tự trở lên.',
            'password.max' => 'Mật khẩu không được quá 20 ký tự.',
            'name.required' => 'Vui lòng nhập tên của bạn.',
            'name.max' => 'Vui lòng nhập tên không quá 255 ký tự.',
        ];
    }

    public function rules()
    {
        $rules = $this->_getRuleDefaults();
        $params = $this->getParams();
        if (Arr::get($params, 'id') && blank(Arr::get($params, 'password'))) {
            unset($rules['password']);
        }
        return $rules;
    }

    public function getRule($field)
    {
        $rules = $this->_getRuleDefaults();

        return isset($rules[$field]) ? [$field => $rules[$field]] : [];
    }

    public function messages()
    {
        return $this->_getMessageDefaults();
    }

    public function getMessage($field)
    {
        $result = [];
        $messages = $this->_getMessageDefaults();
        foreach ($messages as $key => $message) {
            if (strpos($key, $field) !== false) {
                $result[$key] = $message;
            }
        }

        return $result;
    }

    public function validateLogin($params = [])
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];
        $messages = [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Vui lòng nhập đúng định dạng email.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ];
        $validator = Validator::make($params, $rules, $messages);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        return true;
    }
}
