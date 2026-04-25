<?php

namespace App\Http\Requests\Admin\AuthStep;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['sometimes', 'string', 'max:255'],
            'step_type'         => ['sometimes', 'in:login_callback,set_credentials,refresh_token'],
            'auth_type'         => ['sometimes', 'in:oauth2,api_key,basic,bearer,custom'],
            'http_method'       => ['sometimes', 'in:GET,POST,PUT,PATCH,DELETE'],
            'base_endpoint_url' => ['sometimes', 'url'],
            'is_active'         => ['sometimes', 'boolean'],

            'inputs'                    => ['nullable', 'array'],
            'inputs.*.key'              => ['required_with:inputs', 'string'],
            'inputs.*.label'            => ['required_with:inputs', 'string'],
            'inputs.*.type'             => ['required_with:inputs', 'in:text,password,email,url,select,hidden'],
            'inputs.*.required'         => ['required_with:inputs', 'boolean'],
            'inputs.*.placeholder'      => ['nullable', 'string'],
            'inputs.*.options'          => ['nullable', 'array'],
            'inputs.*.options.*.label'  => ['required_with:inputs.*.options', 'string'],
            'inputs.*.options.*.value'  => ['required_with:inputs.*.options', 'string'],

            'outputs'   => ['nullable', 'array'],
            'outputs.*' => ['string'],

            'response_example' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'step_type.in'   => 'step_type must be one of: login_callback, set_credentials, refresh_token.',
            'auth_type.in'   => 'auth_type must be one of: oauth2, api_key, basic, bearer, custom.',
            'http_method.in' => 'http_method must be one of: GET, POST, PUT, PATCH, DELETE.',
        ];
    }
}
