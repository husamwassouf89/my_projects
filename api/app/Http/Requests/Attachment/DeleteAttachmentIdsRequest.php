<?php

namespace App\Http\Requests\Attachment;

use App\Http\Requests\FormRequest;


class DeleteAttachmentIdsRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'required|numeric|exists:attachments,id',
        ];
    }
}
