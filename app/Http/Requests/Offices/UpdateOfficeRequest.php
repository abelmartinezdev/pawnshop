<?php

namespace App\Http\Requests\Offices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfficeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('offices.manage') ?? false;
    }

    public function rules(): array
    {
        $office = $this->route('office');

        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],

            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('offices', 'code')
                    ->ignore($office?->id)
                    ->whereNull('deleted_at'),
            ],
            'serie' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('offices', 'serie')
                    ->ignore($office?->id)
                    ->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'schedule' => ['nullable', 'string', 'max:1000'],
            'bank_account' => ['nullable', 'string', 'max:255'],

            'daily_interest_rate' => ['required', 'numeric', 'min:0'],
            'monthly_interest_rate' => ['required', 'numeric', 'min:0'],
            'cash' => ['nullable', 'numeric'],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_id' => 'empresa',
            'name' => 'nombre',
            'code' => 'código',
            'serie' => 'serie',
            'phone' => 'teléfono',
            'address' => 'dirección',
            'schedule' => 'horario',
            'bank_account' => 'cuenta bancaria',
            'daily_interest_rate' => 'interés diario',
            'monthly_interest_rate' => 'interés mensual',
            'cash' => 'efectivo',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? (int) $this->company_id : null,
            'name' => $this->filled('name') ? trim((string) $this->name) : null,
            'code' => $this->filled('code') ? strtoupper(trim((string) $this->code)) : null,
            'serie' => $this->filled('serie') ? strtoupper(trim((string) $this->serie)) : null,
            'phone' => $this->filled('phone') ? trim((string) $this->phone) : null,
            'address' => $this->filled('address') ? trim((string) $this->address) : null,
            'schedule' => $this->filled('schedule') ? trim((string) $this->schedule) : null,
            'bank_account' => $this->filled('bank_account') ? trim((string) $this->bank_account) : null,
            'daily_interest_rate' => $this->moneyToFloat($this->input('daily_interest_rate', 0)),
            'monthly_interest_rate' => $this->moneyToFloat($this->input('monthly_interest_rate', 0)),
            'cash' => $this->moneyToFloat($this->input('cash', 0)),
        ]);
    }

    private function moneyToFloat(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return round((float) str_replace(['$', ',', ' '], '', (string) $value), 4);
    }
}