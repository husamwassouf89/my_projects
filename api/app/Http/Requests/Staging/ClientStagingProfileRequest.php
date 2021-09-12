<?php

namespace App\Http\Requests\Staging;

use App\Http\Requests\FormRequest;

class ClientStagingProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'client_id'       => 'required|numeric|exists:clients,id',
            'answers'         => 'required|array',
            'answers.*.id'    => 'required|numeric|exists:staging_options,id',
            'answers.*.value' => 'nullable|numeric',
        ];
    }
}
