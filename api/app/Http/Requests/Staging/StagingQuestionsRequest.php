<?php

namespace App\Http\Requests\Staging;

use App\Http\Requests\FormRequest;

class StagingQuestionsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'class_type_id' => 'required|numeric|exists:class_types,id',
            'client_id'     => 'required|numeric|exists:clients,id',
        ];
    }
}
