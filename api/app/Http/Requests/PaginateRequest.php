<?php

namespace App\Http\Requests;


use App\Models\Client\Client;

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
            'page'                => 'numeric|required|min:1',
            'page_size'           => 'numeric|required|min:1',
            'keyword'             => 'nullable|string',
            'class_type_id'       => 'nullable|numeric|exists:class_types,id',
            'financial_status'    => 'nullable|string|in:' . implode(',', Client::$FINANCIAL_STATUS),
            'quarter'             => 'nullable|string',
            'year'                => 'nullable|string',
            'type'                => 'nullable|in:documents',
            'limit'              => 'nullable|in:yes,no',
            'class_type_category' => 'nullable|in:facility,financial',
            'filter_type'         => 'nullable|in:all,with,without'
        ];
    }
}
