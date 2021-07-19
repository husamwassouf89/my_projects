<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class DeleteUserIdsRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'required|numeric|exists:users,id',
        ];

    }
}
