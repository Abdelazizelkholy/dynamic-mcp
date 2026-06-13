<?php


namespace App\Http\Requests\Admin\ServiceInput;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateInputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inputs'                      => ['required', 'array', 'min:1'],
            'inputs.*.id'                 => ['required', 'integer', 'exists:integration_service_inputs,id'],
            'inputs.*.field_type'         => ['sometimes', 'in:input,select,dynamic_select,boolean,group,file,file_url,files,files_url'],
            'inputs.*.key'                => ['sometimes', 'string', 'max:255'],
            'inputs.*.placeholder'        => ['nullable', 'string'],
            'inputs.*.type'               => ['nullable', 'in:text,number,email,password,url,date,textarea'],
            'inputs.*.key_type'           => ['sometimes', 'in:body,query,header,path'],
            'inputs.*.validation'         => ['sometimes', 'in:required,nullable,sometimes'],
            'inputs.*.require_from'       => ['sometimes', 'in:admin,user,front,user_integration,previous_step_response'],
            'inputs.*.options'            => ['nullable', 'array'],
            'inputs.*.options.*'          => ['string'],
            'inputs.*.dynamic_service_id' => ['nullable', 'integer', 'exists:integration_services,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            foreach ($this->input('inputs', []) as $index => $input) {

                if (($input['field_type'] ?? '') === 'select' && empty($input['options'])) {
                    $v->errors()->add("inputs.{$index}.options",
                        'options required when field_type is select.');
                }

                if (($input['field_type'] ?? '') === 'dynamic_select' && empty($input['dynamic_service_id'])) {
                    $v->errors()->add("inputs.{$index}.dynamic_service_id",
                        'dynamic_service_id required when field_type is dynamic_select.');
                }

                if (in_array($input['field_type'] ?? '', ['date', 'datetime']) && empty($input['date_format'])) {
                    $v->errors()->add("inputs.{$index}.date_format",
                        'date_format required when field_type is date or datetime.');
                }
            }
        });
    }
}
