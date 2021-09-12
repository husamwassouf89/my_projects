<?php

namespace App\Http\Requests\IRS;


use App\Http\Requests\FormRequest;

class ClientIRSProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|numeric|exists:clients,id',
            'answers'   => 'required|array',
            'answers.*' => 'required|numeric|exists:options,id',
        ];
    }
}
