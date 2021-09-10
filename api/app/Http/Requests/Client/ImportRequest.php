<?php

namespace App\Http\Requests\Client;

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
            'path'    => 'required|string',
            'year'    => 'required|string|in:' . implode(',', ClassType::getYears()),
            'quarter' => 'required|string|in:' . implode(',', ClassType::$QUARTERS),
        ];
    }
}
