<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\Users;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
