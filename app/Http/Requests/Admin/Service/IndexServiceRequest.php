<?php


namespace App\Http\Requests\Admin\Service;

use Illuminate\Foundation\Http\FormRequest;

class IndexServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
