<?php

namespace App\Http\Requests\Access;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'roles' => ['array'],
            'roles.*' => ['string'],

            'permissions' => ['array'],
            'permissions.*' => ['string'],

            'office_id' => ['nullable', 'integer', Rule::exists('offices', 'id')],
        ];
    }
}