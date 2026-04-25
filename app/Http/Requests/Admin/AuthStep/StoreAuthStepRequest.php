<?php

namespace App\Http\Requests\Admin\AuthStep;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'step_type'         => ['required', 'in:login_callback,set_credentials,refresh_token'],
            'auth_type'         => ['required', 'in:oauth2,api_key,basic,bearer,custom'],
            'http_method'       => ['required', 'in:GET,POST,PUT,PATCH,DELETE'],
            'base_endpoint_url' => ['required', 'url'],
            'is_active'         => ['sometimes', 'boolean'],

            // inputs: array of field definitions the user/admin must fill
            'inputs'                    => ['nullable', 'array'],
            'inputs.*.key'              => ['required_with:inputs', 'string'],
            'inputs.*.label'            => ['required_with:inputs', 'string'],
            'inputs.*.type'             => ['required_with:inputs', 'in:text,password,email,url,select,hidden'],
            'inputs.*.required'         => ['required_with:inputs', 'boolean'],
            'inputs.*.placeholder'      => ['nullable', 'string'],
            'inputs.*.options'          => ['nullable', 'array'],       // for select type
            'inputs.*.options.*.label'  => ['required_with:inputs.*.options', 'string'],
            'inputs.*.options.*.value'  => ['required_with:inputs.*.options', 'string'],

            // outputs: list of keys returned by this step's response
            'outputs'   => ['nullable', 'array'],
            'outputs.*' => ['string'],

            // response_example: free-form JSON shown in UI (code block)
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
