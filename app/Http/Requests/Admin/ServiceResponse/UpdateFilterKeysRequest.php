<?php

namespace App\Http\Requests\Admin\ServiceResponse;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFilterKeysRequest extends FormRequest
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
            'output_filter_keys'           => ['required', 'array'],
            'output_filter_keys.*.key'     => ['required', 'string'],
            'output_filter_keys.*.is_used' => ['required', 'boolean'],
        ];
    }
}
