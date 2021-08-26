<?php

namespace App\Http\Requests\Question;

use App\Http\Requests\FormRequest;

class UpdateQuestionRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'text' => 'required|string',
            'max_options_value' => 'required|numeric|min:0|max:100',
            'options' => 'required|array',
            'options.*.text' => 'required|string',
            'options.*.value' => 'required|numeric|min:0|max:100',
        ];
    }
}
