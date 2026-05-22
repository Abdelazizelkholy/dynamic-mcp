<?php

namespace App\Http\Requests\Admin\Header;

use Illuminate\Foundation\Http\FormRequest;

class StoreHeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'headers'                       => ['required', 'array', 'min:1'],
            'headers.*.type'                => ['required', 'in:normal,bearer,basic_auth'],
            'headers.*.header_key'          => ['required', 'string', 'max:255'],
            'headers.*.concatenate_key'     => ['nullable', 'string', 'max:255'],  // only for type=normal
            'headers.*.require_from'        => ['required', 'in:admin,user,user_integration'],
            'headers.*.value'               => ['nullable', 'string'],
            'headers.*.label'               => ['nullable', 'string', 'max:255'],
            'headers.*.description'         => ['nullable', 'string'],
            'headers.*.is_active'           => ['sometimes', 'boolean'],
        ];
    }

}
