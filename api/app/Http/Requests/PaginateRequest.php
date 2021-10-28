<?php

namespace App\Http\Requests;


/**
 * @property mixed page_size
 */
class PaginateRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'page'          => 'numeric|required|min:1',
            'page_size'     => 'numeric|required|min:1',
            'keyword'       => 'nullable|string',
            'class_type_id' => 'nullable|numeric|exists:class_types,id',
        ];
    }
}
