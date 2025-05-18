<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::rulesStatic();
    }

    public function messages(): array
    {
        return self::messagesStatic();
    }

    public static function rulesStatic(): array
    {
        return [
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'unique:users,email', 'max:100'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    public static function messagesStatic(): array
    {
        return [
            'name.required' => 'お名前を入力してください',
            'name.max' => 'お名前は50文字以内で入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => '有効なメールアドレス（例：user@example.com）を入力してください',
            'email.unique' => 'このメールアドレスはすでに登録されています',
            'email.max' => 'メールアドレスは100文字以内で入力してください',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードと一致しません',
        ];
    }
}