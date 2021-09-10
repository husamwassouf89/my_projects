<?php

namespace App\Http\Requests\PD;

use App\Http\Requests\FormRequest;
use App\Models\Client\ClassType;

class ImportRequest extends FormRequest
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
            'year'          => 'required|string|in:' . implode(',', ClassType::getYears()),
            'quarter'       => 'required|string|in:' . implode(',', ClassType::$QUARTERS),

            'eco_parameter_base_value'  => 'required|numeric|min:0.0|max:1',
            'eco_parameter_mild_value'  => 'required|numeric|min:0.0|max:1',
            'eco_parameter_heavy_value' => 'required|numeric|min:0.0|max:1',

            'eco_parameter_base_weight'  => 'required|numeric|min:0.0|max:100',
            'eco_parameter_mild_weight'  => 'required|numeric|min:0.0|max:100',
            'eco_parameter_heavy_weight' => 'required|numeric|min:0.0|max:100',

            'path'             => 'required|string',
            'attachment_ids'   => 'array',
            'attachment_ids.*' => 'numeric|exists:attachments,id',

        ];
    }
}
