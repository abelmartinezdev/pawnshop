<?php

namespace App\Http\Requests\Departments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pawn.manage') ?? false;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department');

        return [
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('departments', 'code')
                    ->ignore($departmentId)
                    ->whereNull('deleted_at'),
            ],
            'description' => ['required', 'string', 'max:255'],

            'auction' => ['required', 'integer', 'min:1', 'max:360'],
            'term' => ['required', 'integer', 'min:1', 'max:360'],

            'loan_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'daily_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'monthly_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'iva_rate' => ['required', 'numeric', 'min:0', 'max:100'],

            'cat_annual' => ['nullable', 'numeric', 'min:0', 'max:999.999'],
            'cat_annual_noiva' => ['nullable', 'numeric', 'min:0', 'max:999.999'],

            'color' => ['nullable', 'string', 'max:50'],
            'icon' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'código',
            'description' => 'descripción',
            'auction' => 'días para remate',
            'term' => 'días de plazo',
            'loan_rate' => 'porcentaje de préstamo',
            'daily_interest_rate' => 'interés diario',
            'monthly_interest_rate' => 'interés mensual',
            'iva_rate' => 'IVA',
            'cat_annual' => 'CAT anual',
            'cat_annual_noiva' => 'CAT anual sin IVA',
            'color' => 'color',
            'icon' => 'ícono',
            'is_active' => 'estatus',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->filled('code') ? mb_strtoupper(trim((string) $this->code)) : null,
            'description' => $this->filled('description') ? mb_strtoupper(trim((string) $this->description)) : null,
            'auction' => $this->filled('auction') ? (int) $this->auction : null,
            'term' => $this->filled('term') ? (int) $this->term : null,
            'loan_rate' => $this->filled('loan_rate') ? round((float) $this->loan_rate, 3) : null,
            'daily_interest_rate' => $this->filled('daily_interest_rate') ? round((float) $this->daily_interest_rate, 3) : null,
            'monthly_interest_rate' => $this->filled('monthly_interest_rate') ? round((float) $this->monthly_interest_rate, 3) : null,
            'iva_rate' => $this->filled('iva_rate') ? round((float) $this->iva_rate, 3) : null,
            'cat_annual' => $this->filled('cat_annual') ? round((float) $this->cat_annual, 3) : null,
            'cat_annual_noiva' => $this->filled('cat_annual_noiva') ? round((float) $this->cat_annual_noiva, 3) : null,
            'color' => $this->filled('color') ? trim((string) $this->color) : null,
            'icon' => $this->filled('icon') ? trim((string) $this->icon) : null,
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}