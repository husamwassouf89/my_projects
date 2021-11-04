<?php

namespace App\Http\Requests\Staging;

use App\Http\Requests\FormRequest;
use App\Models\Client\Client;
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
            'class_type_id'        => 'required|numeric|exists:class_types,id',
            'text'                 => 'required|string',
            'financial_status'     => 'required|string|in:' . implode(',', Client::$FINANCIAL_STATUS),
            'options'              => 'required|array',
            'options.*.id'         => 'nullable|numeric|exists:options,id',
            'options.*.text'       => 'required|string',
            'options.*.type'       => 'required|string|in:' . implode(',', StagingOption::$TYPES),
            'options.*.with_value' => 'required|string|in:Yes,No',
        ];
    }
}
