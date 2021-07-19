<?php

namespace App\Http\Requests;

class DatesFilterRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules():array
    {
        return [
            'after'  => 'required|date_format:Y/m/d',
            'before' => 'required|date_format:Y/m/d',
        ];
    }
}
