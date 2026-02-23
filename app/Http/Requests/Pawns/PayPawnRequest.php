<?php

namespace App\Http\Requests\Pawns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayPawnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pawn.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'transaction' => ['required', Rule::in(['liquidate','countersign','interest_payment'])],
            'amount_paid' => ['required','numeric','min:0.01'],
            'payment' => ['nullable','numeric','min:0.01'],
            'pay_extra' => ['nullable','numeric','min:0'],
            'payment_type' => ['required', Rule::in(['cash','card','transfer'])], // ajusta
        ];
    }

    protected function prepareForValidation(): void
    {
        // tu viejo input era "pay-extra"
        if ($this->has('pay-extra') && ! $this->has('pay_extra')) {
            $this->merge(['pay_extra' => $this->input('pay-extra')]);
        }
    }
}