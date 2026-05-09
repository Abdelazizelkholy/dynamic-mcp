<?php

namespace App\Http\Requests\Admin\AuthStep;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAuthStepRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'              => ['sometimes', 'string', 'max:255'],
            'step_type'         => ['sometimes', 'in:login_callback,set_credentials,refresh_token'],
            'auth_type'         => ['sometimes', 'in:oauth2,api_key,basic,bearer,custom'],
            'http_method'       => ['sometimes', 'in:GET,POST,PUT,PATCH,DELETE'],
            'base_endpoint_url' => ['sometimes', 'url'],
            'is_active'         => ['sometimes', 'boolean'],

            'inputs'                       => ['nullable', 'array'],
            'inputs.*.key'                 => ['required_with:inputs', 'string'],
            'inputs.*.label'               => ['required_with:inputs', 'string'],
            'inputs.*.type'                => ['required_with:inputs', 'in:text,password,email,url,select,hidden'],
            'inputs.*.required'            => ['required_with:inputs', 'boolean'],
            'inputs.*.placeholder'         => ['nullable', 'string'],
            'inputs.*.require_from'        => [
                'required_with:inputs',
                'in:admin,user,front,response,user_integration,previous_step_response',
            ],
            'inputs.*.value'               => ['nullable', 'string'],
            'inputs.*.options'             => ['nullable', 'array'],
            'inputs.*.options.*.label'     => ['required_with:inputs.*.options', 'string'],
            'inputs.*.options.*.value'     => ['required_with:inputs.*.options', 'string'],

            'outputs'          => ['nullable', 'array'],
            'outputs.*'        => ['string'],
            'response_example' => ['nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            foreach ($this->input('inputs', []) as $index => $input) {
                if (
                    isset($input['require_from']) &&
                    $input['require_from'] === 'previous_step_response' &&
                    empty($input['value'])
                ) {
                    $v->errors()->add(
                        "inputs.{$index}.value",
                        "The value field is required when require_from is 'previous_step_response'."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'inputs.*.require_from.in' => 'require_from must be one of: admin, user, front, response, user_integration, previous_step_response.',
        ];
    }
}
