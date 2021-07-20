<?php

namespace App\Http\Requests\Client\ClassType;


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
            'id' => 'required|numeric|exists:class_types,id'
        ];
    }
}
