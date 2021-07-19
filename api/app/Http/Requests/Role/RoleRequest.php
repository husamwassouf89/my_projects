<?php

namespace App\Http\Requests\Role;


use App\Http\Requests\FormRequest;

class RoleRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'          => 'required|string',
            'permissions'   => 'array',
            'permissions.*' => 'numeric|exists:permissions,id',
        ];
    }
}
