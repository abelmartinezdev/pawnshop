<?php

namespace App\Http\Requests\Incomes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cash.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'payment_type' => ['required', Rule::in(['cash', 'card'])],
            'comments' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'monto',
            'payment_type' => 'tipo de pago',
            'comments' => 'comentarios',
        ];
    }
}