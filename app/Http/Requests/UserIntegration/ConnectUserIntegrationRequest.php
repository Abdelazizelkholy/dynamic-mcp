<?php

namespace App\Http\Requests\UserIntegration;

use Illuminate\Foundation\Http\FormRequest;

class ConnectUserIntegrationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /**
     * Keys are dynamic — they depend on which `require_from: user` inputs the
     * integration's first auth step declares (e.g. api_key for set_credentials).
     */
    public function rules(): array
    {
        return [
            'inputs'   => ['sometimes', 'array'],
            'inputs.*' => ['nullable', 'string'],
        ];
    }
}
