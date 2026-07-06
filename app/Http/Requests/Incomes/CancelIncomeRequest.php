<?php

namespace App\Http\Requests\Incomes;

use Illuminate\Foundation\Http\FormRequest;

class CancelIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cash.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'comments_cancel' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'comments_cancel' => 'motivo de cancelación',
        ];
    }
}