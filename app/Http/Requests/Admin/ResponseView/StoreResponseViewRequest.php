<?php

namespace App\Http\Requests\Admin\ResponseView;

use Illuminate\Foundation\Http\FormRequest;

class StoreResponseViewRequest extends FormRequest
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
            'views'             => ['required', 'array', 'min:1'],
            'views.*.key'       => ['required', 'string'],
            'views.*.data_type' => ['required', 'in:text,file'],
        ];
    }

    public function messages(): array
    {
        return [
            'views.*.data_type.in' => 'data_type must be: text or file.',
        ];
    }
}
