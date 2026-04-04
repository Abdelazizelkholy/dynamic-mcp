<?php

namespace App\Http\Requests\Admin\Integration;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIntegrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'base_api_url' => 'nullable|url',
            'documentation_url' => 'nullable|url',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'publish' => 'nullable|boolean',
            'integration_media' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ];
    }
}
