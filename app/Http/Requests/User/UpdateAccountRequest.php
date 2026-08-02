<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'email'           => ['sometimes', 'required', 'email', 'unique:users,email,'.$userId],
            'password'        => ['nullable', 'string', 'min:6', 'confirmed'],
            'phone'           => ['nullable', 'string'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
