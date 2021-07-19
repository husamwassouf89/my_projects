<?php

namespace App\Http\Requests\User;


use App\Http\Requests\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email'       => 'required|string|email|unique:users,email,' . request('id'),
            'password'    => 'required|string|min:6',
            "employee_id" => 'required|numeric|exists:employees,id',
            "role_id"     => 'required|numeric|exists:roles,id',
        ];
    }
}
