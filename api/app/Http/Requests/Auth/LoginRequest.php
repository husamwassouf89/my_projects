<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\FormRequest;

class LoginRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email'    => 'required|string|email|exists:users,email',
            'password' => 'required|string|min:6',
        ];

    }
}
