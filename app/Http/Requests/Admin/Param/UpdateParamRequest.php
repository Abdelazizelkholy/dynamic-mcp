<?php


namespace App\Http\Requests\Admin\Param;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'in:static,user_integration'],
            'value' => ['sometimes', 'string', 'max:500'],
        ];
    }
}
