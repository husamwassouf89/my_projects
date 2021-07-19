<?php

namespace App\Http\Requests\Role;


use App\Http\Requests\FormRequest;

class DeleteRoleIdsRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids' => 'required|array',
            'ids.*' => 'required|numeric|exists:roles,id',
        ];
    }
}
