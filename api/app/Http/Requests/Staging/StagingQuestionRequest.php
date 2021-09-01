<?php

namespace App\Http\Requests\Staging;

use App\Http\Requests\FormRequest;
use App\Models\Staging\StagingOption;

class StagingQuestionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'class_type_id'   => 'required|numeric|exists:class_types,id',
            'text'            => 'required|string',
            'options'         => 'required|array',
            'options.*.id'    => 'nullable|numeric|exists:options,id',
            'options.*.text'  => 'required|string',
            'options.*.type' => 'required|string|in:' . StagingOption::getTypes(),
        ];
    }
}
