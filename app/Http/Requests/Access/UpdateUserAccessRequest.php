<?php

namespace App\Http\Requests\Access;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserAccessRequest extends FormRequest
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

            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ];
    }
}