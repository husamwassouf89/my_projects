<?php

namespace App\Http\Requests\Client;


use App\Http\Requests\FormRequest;

class ClientIdRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'numeric|exists:clients,id',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->route('clients'))
            $this->merge(['id' => $this->route('clients')]);
    }
}
