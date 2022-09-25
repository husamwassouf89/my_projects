<?php

namespace App\Http\Requests;

class AttachmentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'attachment_ids'   => 'array|required',
            'attachment_ids.*' => 'required|numeric|exists:attachments,id',
        ];
    }
}
