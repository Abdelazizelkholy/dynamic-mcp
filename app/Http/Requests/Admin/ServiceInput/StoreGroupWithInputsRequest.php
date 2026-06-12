<?php


namespace App\Http\Requests\Admin\ServiceInput;

use Illuminate\Foundation\Http\FormRequest;

class StoreInputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    
   public function rules(): array
{
    return [
        'groups'                                    => ['required', 'array', 'min:1'],
        'groups.*.key_name'                         => ['required', 'string', 'max:255'],
        'groups.*.data_type'                        => ['required', 'in:object,array_of_objects,array'],
        'groups.*.inputs'                           => ['nullable', 'array'],
        'groups.*.inputs.*.field_type'              => ['required_with:groups.*.inputs', 'in:input,select,dynamic_select,boolean,group,file,file_url,files,files_url'],
        'groups.*.inputs.*.key'                     => ['required_with:groups.*.inputs', 'string', 'max:255'],
        'groups.*.inputs.*.placeholder'             => ['nullable', 'string'],
        'groups.*.inputs.*.type'                    => ['nullable', 'in:text,number,email,password,url,date,textarea'],
        'groups.*.inputs.*.key_type'                => ['required_with:groups.*.inputs', 'in:body,query,header,path'],
        'groups.*.inputs.*.validation'              => ['required_with:groups.*.inputs', 'in:required,nullable,sometimes'],
        'groups.*.inputs.*.require_from'            => ['required_with:groups.*.inputs', 'in:admin,user,front,user_integration,previous_step_response'],
        'groups.*.inputs.*.options'                 => ['nullable', 'array'],
        'groups.*.inputs.*.options.*'               => ['string'],
        'groups.*.inputs.*.dynamic_service_id'      => ['nullable', 'integer', 'exists:integration_services,id'],

        // Nested group inside group
        'groups.*.inputs.*.group'                   => ['nullable', 'array'],
        'groups.*.inputs.*.group.key_name'          => ['required_with:groups.*.inputs.*.group', 'string'],
        'groups.*.inputs.*.group.data_type'         => ['required_with:groups.*.inputs.*.group', 'in:object,array_of_objects,array'],
        'groups.*.inputs.*.group.inputs'            => ['nullable', 'array'],
    ];
}

}
