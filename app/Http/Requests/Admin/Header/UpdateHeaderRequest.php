<?php

namespace App\Http\Requests\Admin\Header;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'         => ['sometimes', 'in:normal,bearer,basic_auth'],
            'header_key'   => ['sometimes', 'string', 'max:255'],
            'require_from' => ['sometimes', 'in:admin,user_integration'],
            'value'        => ['nullable', 'string'],
            'label'        => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'is_active'    => ['sometimes', 'boolean'],
        ];
    }
}
