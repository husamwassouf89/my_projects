<?php

namespace App\Http\Requests\User;


use App\Http\Requests\FormRequest;

class UpdateSessionUserRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:700',
            'mobile'   => 'required|string|max:700',
            'email'    => 'required|string|email',
            'password' => 'nullable|string|min:6',
        ];
    }
}
