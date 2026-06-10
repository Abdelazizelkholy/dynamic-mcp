<?php

namespace App\Http\Requests\Admin\Service;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'service_name_en'           => ['sometimes', 'string', 'max:255'],
            'service_name_ar'           => ['required', 'string', 'max:255'],
            'http_method'            => ['sometimes', 'in:GET,POST,PUT,PATCH,DELETE'],
            'content_type'           => ['sometimes', 'in:application/json,multipart/form-data,application/x-www-form-urlencoded,text/plain'],
            'endpoint_path'          => ['sometimes', 'string', 'max:500'],
            'logo'                   => ['nullable', 'file', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'base_url_override'      => ['nullable', 'url'],
            'description_en'         => ['nullable', 'string'],
            'description_ar'         => ['nullable', 'string'],
            'is_enabled'             => ['sometimes', 'boolean'],
            'inherit_global_headers' => ['sometimes', 'boolean'],
            'long_term_execution'    => ['sometimes', 'boolean'],
            'response_example'       => ['sometimes', 'array'],

            'dependency_service_ids'   => ['nullable', 'array'],
            'dependency_service_ids.*' => ['integer', 'exists:integration_services,id'],
        ];
    }
}
