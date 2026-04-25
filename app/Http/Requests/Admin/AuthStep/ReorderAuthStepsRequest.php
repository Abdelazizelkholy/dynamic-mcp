<?php

namespace App\Http\Requests\Admin\AuthStep;

use Illuminate\Foundation\Http\FormRequest;

class ReorderAuthStepsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ordered_ids'   => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['required', 'integer', 'exists:integration_auth_steps,id'],
        ];
    }
}
