<?php


namespace App\Http\Requests\Admin\ServiceInput;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key_name' => ['required', 'string', 'max:255'],
            'data_type' => ['required', 'in:object,array_of_objects,array'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_type.in' => 'data_type must be one of: object, array_of_objects, array.',
        ];
    }
}
