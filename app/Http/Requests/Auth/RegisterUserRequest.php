<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'O campo de nome é obrigatório.',

            'email.unique' => 'Já existe um e-mail semelhante cadastrado.',
            'email.required' => 'O campo de e-mail é obrigatório.',
            'email.email' => 'Adicione um e-mail válido.',

            'password.required' => 'O campo de senha é obrigatório.',
            'password.confirmed' => 'Verique a senha digitada.',
            'password.min' => 'A precisa ter no mínimo 8 caracteres.'
        ];
    }
}
