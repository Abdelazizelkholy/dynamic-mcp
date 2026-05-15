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
            'field_type' => ['sometimes', 'in:input,select,dynamic_select,boolean,group,file,file_url,files,files_url'],
            'key' => ['sometimes', 'string', 'max:255'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:text,number,email,password,url,date,textarea'],
            'key_type' => ['sometimes', 'in:body,query,header,path'],
            'validation' => ['sometimes', 'in:required,nullable,sometimes'],
            'require_from' => ['sometimes', 'in:admin,user,front,user_integration,previous_step_response'],
            'options' => ['nullable', 'array'],
            'options.*' => ['string'],
            'dynamic_service_id' => ['nullable', 'integer', 'exists:integration_services,id'],
        ];
    }

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
}
