<?php


namespace App\Http\Requests\Admin\ServiceInput;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key_name' => ['sometimes', 'string', 'max:255'],
            'data_type' => ['sometimes', 'in:object,array_of_objects,array'],
        ];
    }
}
