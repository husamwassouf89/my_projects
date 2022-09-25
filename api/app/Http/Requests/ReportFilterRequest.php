<?php

namespace App\Http\Requests;

class ReportFilterRequest extends FormRequest
{

    public function rules()
    {
        return [
            'quarter1'            => 'required|string',
            'year1'               => 'required|string',
            'quarter2'            => 'string|nullable',
            'year2'               => 'string|nullable',
            'type'                => 'nullable|in:documents',
            'limits'              => 'required|in:yes,no',
            'class_type_category' => 'required|in:facility,financial',
        ];
    }
}
