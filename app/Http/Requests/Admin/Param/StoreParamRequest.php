<?php

namespace App\Http\Requests\Admin\Param;

use Illuminate\Foundation\Http\FormRequest;

class StoreParamRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'params'        => ['required', 'array'],
            'params.*.type' => ['required', 'in:static,user_integration'],
            'params.*.value'=> ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'type must be: static or user_integration.',
        ];
    }
}
