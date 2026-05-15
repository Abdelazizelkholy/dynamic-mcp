<?php


namespace App\Http\Requests\Admin\ServiceHeader;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceHeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:normal,bearer,basic_auth'],
            'header_key' => ['required', 'string', 'max:255'],
            'require_from' => ['required', 'in:admin,user,user_integration'],
            'value' => ['nullable', 'string'],
            'label' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'type must be one of: normal, bearer, basic_auth.',
            'require_from.in' => 'require_from must be one of: admin, user, user_integration.',
        ];
    }
}
