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
            'group_id' => ['nullable', 'integer', 'exists:integration_service_input_groups,id'],
            'field_type' => ['required', 'in:input,select,dynamic_select,boolean,group,file,file_url,files,files_url'],
            'key' => ['required', 'string', 'max:255'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:text,number,email,password,url,date,textarea'],
            'key_type' => ['required', 'in:body,query,header,path'],
            'validation' => ['required', 'in:required,nullable,sometimes'],
            'require_from' => ['required', 'in:admin,user,front,user_integration,previous_step_response'],

            // For select — static options array
            'options' => ['nullable', 'array'],
            'options.*' => ['string'],

            // For dynamic_select — references another service
            'dynamic_service_id' => ['nullable', 'integer', 'exists:integration_services,id'],
        ];
    }

    /**
     * Conditional validation:
     * - field_type = select       → options required
     * - field_type = dynamic_select → dynamic_service_id required
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $fieldType = $this->input('field_type');

            if ($fieldType === 'select' && empty($this->input('options'))) {
                $v->errors()->add('options', 'options is required when field_type is select.');
            }

            if ($fieldType === 'dynamic_select' && empty($this->input('dynamic_service_id'))) {
                $v->errors()->add('dynamic_service_id', 'dynamic_service_id is required when field_type is dynamic_select.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'field_type.in' => 'field_type must be one of: input, select, dynamic_select, boolean, group, file, file_url, files, files_url.',
            'key_type.in' => 'key_type must be one of: body, query, header, path.',
            'validation.in' => 'validation must be one of: required, nullable, sometimes.',
            'require_from.in' => 'require_from must be one of: admin, user, front, user_integration, previous_step_response.',
            'type.in' => 'type must be one of: text, number, email, password, url, date, textarea.',
        ];
    }
}
