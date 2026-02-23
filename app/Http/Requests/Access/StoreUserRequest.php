<?php

namespace App\Http\Requests\Access;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required', 'string', 'email', 'max:190',
                Rule::unique('users', 'email'),
            ],

            // ✅ ahora es sucursal
            'office_id' => ['required', 'integer', Rule::exists('offices', 'id')],

            'password' => ['nullable', 'string', 'min:8', 'max:72'],

            'roles' => ['array'],
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],

            'permissions' => ['array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where('guard_name', 'web'),
            ],
        ];
    }
}