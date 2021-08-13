<?php

namespace App\Http\Requests\Attachment;

use App\Http\Requests\FormRequest;

class UploadRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required|string|in:pd,attachments'
        ];
    }
}
