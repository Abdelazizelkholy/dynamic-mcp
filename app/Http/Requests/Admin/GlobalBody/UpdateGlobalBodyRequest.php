<?php

namespace App\Http\Requests\Admin\GlobalBody;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGlobalBodyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'body'                  => ['required', 'array', 'min:1'],
            'body.*.key'            => ['required', 'string', 'max:255'],
            'body.*.require_from'   => ['required', 'in:admin,user_integration'],
            'body.*.value'          => ['nullable', 'string'],
            'body.*.label'          => ['nullable', 'string', 'max:255'],
            'body.*.description'    => ['nullable', 'string'],
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
