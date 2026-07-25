<?php

namespace App\Http\Requests\Admin\Integration;

use Illuminate\Foundation\Http\FormRequest;

class ImportPostmanCollectionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /**
     * Accepts either an uploaded .json file (`file`) or the collection JSON
     * pasted directly in the request body (`collection`).
     */
    public function rules(): array
    {
        return [
            'file'       => ['required_without:collection', 'file'],
            'collection' => ['required_without:file', 'array'],
        ];
    }
}
