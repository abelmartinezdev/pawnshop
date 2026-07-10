<?php

namespace App\Http\Requests\Pawns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LiquidatePawnWithDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()?->can('apply-discount');
    }

    public function rules(): array
    {
        return [
            'discount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:100',
            ],
            'amount_paid' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'payment_type' => [
                'required',
                'string',
                Rule::in(array_keys($this->paymentTypes())),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'discount' => 'descuento',
            'amount_paid' => 'monto recibido',
            'payment_type' => 'tipo de pago',
        ];
    }

    public function messages(): array
    {
        return [
            'discount.required' => 'Captura el porcentaje de descuento.',
            'discount.min' => 'El descuento debe ser mayor a 0.',
            'discount.max' => 'El descuento no puede ser mayor al 100%.',
            'amount_paid.required' => 'Captura el monto recibido.',
            'amount_paid.min' => 'El monto recibido debe ser mayor a 0.',
            'payment_type.required' => 'Selecciona el tipo de pago.',
        ];
    }

    private function paymentTypes(): array
    {
        $types = config('core.payment_types', []);

        if (is_array($types) && count($types) > 0) {
            return $types;
        }

        return [
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
        ];
    }
}