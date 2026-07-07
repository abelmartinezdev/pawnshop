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
            'transaction' => [
                'required',
                'string',
                Rule::in([
                    'liquidation',
                    'liquidate',
                    'countersign',
                    'interest_payment',
                ]),
            ],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pay_extra' => ['nullable', 'numeric', 'min:0'],
            'payment' => ['nullable', 'numeric', 'min:0'],
            'amount_due' => ['required', 'numeric', 'min:0.01'],
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'payment_type' => [
                'required',
                'string',
                Rule::in(['cash', 'card', 'transfer']),
            ],
            'change' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'transaction' => 'transacción',
            'discount' => 'descuento',
            'pay_extra' => 'abono a capital',
            'payment' => 'abono a interés',
            'amount_due' => 'total a pagar',
            'amount_paid' => 'pago recibido',
            'payment_type' => 'tipo de pago',
            'change' => 'cambio',
        ];
    }

    protected function prepareForValidation(): void
    {
        $transaction = (string) $this->input('transaction', '');

        if ($transaction === 'liquidate') {
            $transaction = 'liquidation';
        }

        $this->merge([
            'transaction' => $transaction,
            'discount' => $this->moneyToFloat($this->input('discount', 0)),
            'pay_extra' => $this->moneyToFloat($this->input('pay_extra', 0)),
            'payment' => $this->moneyToFloat($this->input('payment', 0)),
            'amount_due' => $this->moneyToFloat($this->input('amount_due', 0)),
            'amount_paid' => $this->moneyToFloat($this->input('amount_paid', 0)),
            'change' => $this->moneyToFloat($this->input('change', 0)),
        ]);
    }

    private function moneyToFloat(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $value = str_replace(['$', ',', ' '], '', (string) $value);

        return round((float) $value, 2);
    }
}