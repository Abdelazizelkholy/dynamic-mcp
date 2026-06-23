<?php

namespace App\Http\Requests\Admin\ServiceResponse;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceResponseRequest extends FormRequest
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
            'response_example' => ['required', 'array'],
        ];
    }
}
