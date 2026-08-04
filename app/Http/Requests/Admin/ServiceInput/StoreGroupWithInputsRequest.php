<?php

namespace App\Http\Requests\Admin\ServiceInput;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreGroupWithInputsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->isJson()) {
            $data = json_decode($this->getContent(), true);
            if (is_array($data)) {
                $this->merge($data);
            }
        }
    }

    public function rules(): array
    {
        return [
            'groups'                               => ['required', 'array', 'min:1'],
            'groups.*.key_name'                    => ['required', 'string', 'max:255'],
            'groups.*.data_type'                   => ['required', 'in:object,array_of_objects,array'],
            'groups.*.inputs'                      => ['nullable', 'array'],
            'groups.*.inputs.*.field_type'         => ['nullable', 'in:input,select,dynamic_select,boolean,group,file,files,date,datetime'],
            'groups.*.inputs.*.key'                => ['nullable', 'string', 'max:255'],
            'groups.*.inputs.*.placeholder'        => ['nullable', 'string'],
            'groups.*.inputs.*.type'               => ['nullable', 'in:params,input'],
            'groups.*.inputs.*.key_type'           => ['nullable', 'in:body,headers'],
            'groups.*.inputs.*.validation'         => ['nullable', 'in:required,nullable'],
            'groups.*.inputs.*.require_from'       => ['nullable', 'in:admin,user,front,response,user_integration,dependency_service'],
            'groups.*.inputs.*.label'              => ['nullable', 'string', 'max:255'],
            'groups.*.inputs.*.options'            => ['nullable', 'array'],
            'groups.*.inputs.*.options.*'          => ['string'],
            'groups.*.inputs.*.dynamic_service_id' => ['nullable', 'integer', 'exists:integration_services,id'],
            'groups.*.inputs.*.date_format'        => ['nullable', 'string', 'max:50'],

            // Nested group — directly without wrapper
            'groups.*.inputs.*.group'              => ['nullable', 'array'],
            'groups.*.inputs.*.group.key_name'     => ['nullable', 'string'],
            'groups.*.inputs.*.group.data_type'    => ['nullable', 'in:object,array_of_objects,array'],
            'groups.*.inputs.*.group.inputs'       => ['nullable', 'array'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach ($this->input('groups', []) as $gIndex => $group) {
                    foreach ($group['inputs'] ?? [] as $iIndex => $input) {

                        // Nested group — skip field_type checks
                        if (! empty($input['group'])) continue;

                        $ft = $input['field_type'] ?? '';

                        // Required fields for regular inputs
                        if (empty($ft) || empty($input['key']) || empty($input['key_type'])) {
                            $validator->errors()->add(
                                "groups.{$gIndex}.inputs.{$iIndex}.field_type",
                                'field_type, key, key_type are required for each input.'
                            );
                        }

                        if ($ft === 'select' && empty($input['options'])) {
                            $validator->errors()->add(
                                "groups.{$gIndex}.inputs.{$iIndex}.options",
                                'options required when field_type is select.'
                            );
                        }

                        if ($ft === 'dynamic_select' && empty($input['dynamic_service_id'])) {
                            $validator->errors()->add(
                                "groups.{$gIndex}.inputs.{$iIndex}.dynamic_service_id",
                                'dynamic_service_id required when field_type is dynamic_select.'
                            );
                        }

                        if (in_array($ft, ['date', 'datetime']) && empty($input['date_format'])) {
                            $validator->errors()->add(
                                "groups.{$gIndex}.inputs.{$iIndex}.date_format",
                                'date_format required when field_type is date or datetime.'
                            );
                        }
                    }
                }
            }
        ];
    }
}
