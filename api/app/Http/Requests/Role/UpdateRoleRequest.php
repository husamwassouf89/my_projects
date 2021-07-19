<?php

namespace App\Http\Requests\Role;


use App\Http\Requests\FormRequest;

class UpdateRoleRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'            => 'required|numeric|exists:roles,id',
            'name'          => 'required|string',
            'permissions'   => 'array',
            'permissions.*' => 'numeric|exists:permissions,id',
        ];
    }
}
