<?php

namespace App\Http\Requests\Admin\AuthStep;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAuthStepRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'step_type'         => ['required', 'in:login_callback,set_credentials,refresh_token'],
            'auth_type'         => ['required', 'in:oauth2,api_key,basic,bearer,custom'],
            'http_method'       => ['required', 'in:GET,POST,PUT,PATCH,DELETE'],
            'base_endpoint_url' => ['required', 'url'],
            'is_active'         => ['sometimes', 'boolean'],

            // ── Inputs ──────────────────────────────────────────────────────
            'inputs'                       => ['nullable', 'array'],
            'inputs.*.key'                 => ['required_with:inputs', 'string'],
            'inputs.*.label'               => ['required_with:inputs', 'string'],
            'inputs.*.type'                => ['required_with:inputs', 'in:text,password,email,url,select,hidden'],
            'inputs.*.required'            => ['required_with:inputs', 'boolean'],
            'inputs.*.placeholder'         => ['nullable', 'string'],

            /*
            |------------------------------------------------------------------
            | require_from — where this input's value comes from
            |------------------------------------------------------------------
            | admin                 → admin sets it manually (static)
            | user                  → end user provides it at runtime
            | front                 → frontend passes it automatically
            | response              → taken from the current step's response
            | user_integration      → taken from user's integration stored data
            | previous_step_response→ taken from a previous auth step's output
            |                         ⚠ when selected, 'value' key is REQUIRED
            |                           and must reference a valid output key
            */
            'inputs.*.require_from' => [
                'required_with:inputs',
                'in:admin,user,front,response,user_integration,previous_step_response',
            ],

            // 'value' is required when require_from = previous_step_response
            // This is enforced in withValidator() below
            'inputs.*.value'        => ['nullable', 'string'],

            // Options for select type
            'inputs.*.options'          => ['nullable', 'array'],
            'inputs.*.options.*.label'  => ['required_with:inputs.*.options', 'string'],
            'inputs.*.options.*.value'  => ['required_with:inputs.*.options', 'string'],

            // ── Outputs ─────────────────────────────────────────────────────
            'outputs'   => ['nullable', 'array'],
            'outputs.*' => ['string'],

            // ── Response example ─────────────────────────────────────────────
            'response_example' => ['nullable', 'array'],
        ];
    }

    /**
     * Conditional validation:
     * If any input has require_from = 'previous_step_response', its 'value' must be present.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $inputs = $this->input('inputs', []);

            foreach ($inputs as $index => $input) {
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
            'step_type.in'          => 'step_type must be one of: login_callback, set_credentials, refresh_token.',
            'auth_type.in'          => 'auth_type must be one of: oauth2, api_key, basic, bearer, custom.',
            'http_method.in'        => 'http_method must be one of: GET, POST, PUT, PATCH, DELETE.',
            'inputs.*.require_from.in' => 'require_from must be one of: admin, user, front, response, user_integration, previous_step_response.',
        ];
    }
}
