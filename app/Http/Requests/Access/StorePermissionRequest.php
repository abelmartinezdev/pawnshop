<?php

namespace App\Http\Requests\Access;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('roles.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:120',
                // convención: modulo.accion (ej: access.permissions.manage)
                'regex:/^[a-z0-9]+([._-][a-z0-9]+)*$/',
                Rule::unique('permissions', 'name')->where('guard_name', 'web'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Formato inválido. Usa minúsculas, números y separadores como "." (ej: companies.manage).',
        ];
    }
}