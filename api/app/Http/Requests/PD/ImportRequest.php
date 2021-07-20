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
            'year'          => 'required|digits:4|integer|min:2000|max:' . (date('Y') + 1),
            'quarter'       => 'required|string|in:' . implode(',', ClassType::$QUARTERS),
            'file'          => 'required',
            'attachment_ids'   => 'array',
            'attachment_ids.*' => 'numeric|exists:attachments,id',

        ];
    }
}
