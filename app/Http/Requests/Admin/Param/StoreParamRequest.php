<?php

namespace App\Http\Requests\Admin\Param;

use App\Models\IntegrationServiceInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreParamRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'params'            => ['required', 'array'],
            'params.*.type'     => ['required', 'in:static,user_integration,params'],
            'params.*.value'    => ['nullable', 'string', 'max:500'],
            'params.*.input_id' => ['nullable', 'integer', 'exists:integration_service_inputs,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'params.*.type.in' => 'type must be: static, user_integration or params.',
        ];
    }

    /**
     * type = params → input_id is required and must belong to this service.
     * type = static | user_integration → value is required.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $serviceId = (int) $this->route('serviceId');

            foreach ($this->input('params', []) as $index => $param) {
                $type = $param['type'] ?? null;

                if ($type === 'params') {
                    if (empty($param['input_id'])) {
                        $v->errors()->add("params.{$index}.input_id", 'input_id is required when type is params.');
                        continue;
                    }

                    $belongsToService = IntegrationServiceInput::where('id', $param['input_id'])
                        ->where('integration_service_id', $serviceId)
                        ->exists();

                    if (! $belongsToService) {
                        $v->errors()->add("params.{$index}.input_id", 'input_id must reference an input belonging to this service.');
                    }
                } elseif (empty($param['value'])) {
                    $v->errors()->add("params.{$index}.value", 'value is required when type is static or user_integration.');
                }
            }
        });
    }
}
