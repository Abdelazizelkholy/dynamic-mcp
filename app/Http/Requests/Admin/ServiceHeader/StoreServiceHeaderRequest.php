<?php


namespace App\Http\Requests\Admin\ServiceHeader;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;


class StoreServiceHeaderRequest extends FormRequest
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
        'headers.*.concatenate_key'     => ['nullable', 'string', 'max:255'],
        'headers.*.require_from'        => ['required', 'in:admin,user,user_integration'],
        'headers.*.value'               => ['nullable', 'string'],
        'headers.*.label'               => ['nullable', 'string', 'max:255'],
        'headers.*.description'         => ['nullable', 'string'],
        'headers.*.is_active'           => ['sometimes', 'boolean'],
    ];
}

public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $v) {
        foreach ($this->input('headers', []) as $index => $header) {
            if (
                isset($header['type']) &&
                $header['type'] === 'basic_auth' &&
                empty($header['label'])
            ) {
                $v->errors()->add(
                    "headers.{$index}.label",
                    "label is required when type is basic_auth. Must be: username or password."
                );
            }

            if (
                isset($header['type'], $header['label']) &&
                $header['type'] === 'basic_auth' &&
                ! in_array(strtolower($header['label']), ['username', 'password'])
            ) {
                $v->errors()->add(
                    "headers.{$index}.label",
                    "label must be 'username' or 'password' when type is basic_auth."
                );
            }
        }
    });
}
}
