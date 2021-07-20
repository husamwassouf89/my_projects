<?php

namespace App\Http\Requests\PD;

use App\Http\Requests\FormRequest;

class IdRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|numeric|exists:p_d_s,id'
        ];
    }
}
