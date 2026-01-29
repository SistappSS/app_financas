<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'exists:users,email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'O campo de e-mail é obrigatório.',
            'password.required' => 'O campo de senha é obrigatório.',

            'email.email' => 'Adicione um e-mail válido.',
            'password.min' => 'A precisa ter no mínimo 6 caracteres.'
        ];
    }
}
