<?php

namespace App\Http\Requests\Admin\ServiceInput;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateGroupWithInputsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'groups'                                    => ['required', 'array', 'min:1'],
            'groups.*.id'                               => ['required', 'integer', 'exists:integration_service_input_groups,id'],
            'groups.*.key_name'                         => ['sometimes', 'string', 'max:255'],
            'groups.*.data_type'                        => ['sometimes', 'in:object,array_of_objects,array'],
            'groups.*.inputs'                           => ['nullable', 'array'],
            'groups.*.inputs.*.id'                      => ['nullable', 'integer', 'exists:integration_service_inputs,id'],

            // ✅ كل fields بـ sometimes — لأن لو id موجود مش لازم نبعت كل حاجة
            'groups.*.inputs.*.field_type'              => ['sometimes', 'in:input,select,dynamic_select,boolean,group,file,file_url,files,files_url,date,datetime'],
            'groups.*.inputs.*.key'                     => ['sometimes', 'string', 'max:255'],
            'groups.*.inputs.*.placeholder'             => ['nullable', 'string'],
            'groups.*.inputs.*.type'                    => ['nullable', 'in:params,input'],
            'groups.*.inputs.*.key_type'                => ['sometimes', 'in:body,headers'],
            'groups.*.inputs.*.validation'              => ['sometimes', 'in:required,nullable'],
            'groups.*.inputs.*.require_from'            => ['sometimes', 'in:admin,user,front,response,user_integration,dependency_service'],
            'groups.*.inputs.*.options'                 => ['nullable', 'array'],
            'groups.*.inputs.*.options.*'               => ['string'],
            'groups.*.inputs.*.dynamic_service_id'      => ['nullable', 'integer', 'exists:integration_services,id'],
            'groups.*.inputs.*.date_format'             => ['nullable', 'string', 'max:50'],

            // Nested group
            'groups.*.inputs.*.group'                   => ['nullable', 'array'],
            'groups.*.inputs.*.group.id'                => ['nullable', 'integer', 'exists:integration_service_input_groups,id'],
            'groups.*.inputs.*.group.key_name'          => ['sometimes', 'string'],
            'groups.*.inputs.*.group.data_type'         => ['sometimes', 'in:object,array_of_objects,array'],
            'groups.*.inputs.*.group.inputs'            => ['nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            foreach ($this->input('groups', []) as $gIndex => $group) {
                foreach ($group['inputs'] ?? [] as $iIndex => $input) {
                    $ft        = $input['field_type'] ?? '';
                    $hasId     = ! empty($input['id']);

                    // لو موجود id → update جزئي → مش محتاجين conditional checks
                    if ($hasId) continue;

                    // لو input جديد → نتحقق
                    if ($ft === 'select' && empty($input['options'])) {
                        $v->errors()->add(
                            "groups.{$gIndex}.inputs.{$iIndex}.options",
                            'options required when field_type is select.'
                        );
                    }

                    if ($ft === 'dynamic_select' && empty($input['dynamic_service_id'])) {
                        $v->errors()->add(
                            "groups.{$gIndex}.inputs.{$iIndex}.dynamic_service_id",
                            'dynamic_service_id required when field_type is dynamic_select.'
                        );
                    }

                    if (in_array($ft, ['date', 'datetime']) && empty($input['date_format'])) {
                        $v->errors()->add(
                            "groups.{$gIndex}.inputs.{$iIndex}.date_format",
                            'date_format required when field_type is date or datetime.'
                        );
                    }
                }
            }
        });
    }
}
