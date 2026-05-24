<?php

namespace App\Http\Requests\Admin\GlobalBody;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGlobalBodyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    // UpdateGlobalBodyRequest
    public function rules(): array
    {
        return [
            'key'          => ['sometimes', 'string', 'max:255'],
            'require_from' => ['sometimes', 'in:admin,user_integration'],
            'value'        => ['nullable', 'string'],
            'label'        => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.*.require_from.in' => 'require_from must be: admin or user_integration.',
            'body.*.key.required'    => 'Each body item must have a key.',
        ];
    }
}
