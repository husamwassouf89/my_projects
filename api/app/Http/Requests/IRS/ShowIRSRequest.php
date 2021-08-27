<?php

namespace App\Http\Requests\IRS;

use App\Http\Requests\FormRequest;

class ShowIRSRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'class_type_id' => 'required|numeric|exists:class_types,id',
            'category_id'   => 'required|numeric|exists:categories,id',
        ];
    }
}
