<?php

namespace App\Http\Requests\UserIntegration;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
