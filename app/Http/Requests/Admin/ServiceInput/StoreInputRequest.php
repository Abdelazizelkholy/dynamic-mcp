<?php


namespace App\Http\Requests\Admin\ServiceInput;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreInputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'inputs' => ['required', 'array', 'min:1'],
            'inputs.*.key' => ['required', 'string', 'max:255'],
            'inputs.*.placeholder' => ['nullable', 'string', 'max:255'],
            'inputs.*.options' => ['nullable', 'array'],
            'inputs.*.options.*' => ['string'],
            'inputs.*.dynamic_service_id' => ['nullable', 'integer', 'exists:integration_services,id'],

            /////////////////////////////
            // field_type — add date, datetime
            'inputs.*.field_type' => ['required', 'in:input,select,dynamic_select,boolean,group,file,file_url,files,files_url,date,datetime'],
            'inputs.*.date_format' => ['required', 'string', 'max:50'],
            'inputs.*.type' => ['required', 'in:params,input'],
            'inputs.*.key_type' => ['required', 'in:body,headers'],
            'inputs.*.validation' => ['required', 'in:required,nullable'],
            'inputs.*.require_from' => ['required', 'in:admin,user,front,response,user_integration,dependency_service'],


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
