<?php

namespace App\Http\Requests\Staging;

use App\Models\Staging\StagingOption;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStagingQuestionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'text'            => 'required|string',
            'options'         => 'required|array',
            'options.*.id'    => 'nullable|numeric|exists:options,id',
            'options.*.text'  => 'required|string',
            'options.*.type'  => 'required|string|in:' . implode(',', StagingOption::$TYPES),
            'options.*.value' => 'required|string|in:Yes,No',
        ];
    }


}
