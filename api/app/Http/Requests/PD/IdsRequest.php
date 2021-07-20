<?php

namespace App\Http\Requests\PD;

use App\Http\Requests\FormRequest;

class IdsRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'required|numeric|exists:p_d_s,id',
        ];
    }
}
