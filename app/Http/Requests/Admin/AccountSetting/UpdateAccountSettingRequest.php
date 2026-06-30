<?php


namespace App\Http\Requests\Admin\AccountSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'base_url'         => ['required', 'url', 'max:500'],
            'http_method'      => ['required', 'in:GET,POST,PUT,PATCH'],
            'email_key'        => ['required', 'string', 'max:255'],
            'response_example' => ['nullable', 'array'],
        ];
    }
}
