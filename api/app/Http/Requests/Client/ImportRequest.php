<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\FormRequest;

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
            'file' => 'required'
        ];
    }
}
