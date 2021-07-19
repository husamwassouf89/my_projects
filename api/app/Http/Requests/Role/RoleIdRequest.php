<?php

namespace App\Http\Requests\Role;


use App\Http\Requests\FormRequest;

class RoleIdRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|numeric|exists:roles,id',
        ];
    }
}
