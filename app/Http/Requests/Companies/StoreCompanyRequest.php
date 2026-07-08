<?php

namespace App\Http\Requests\Companies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('companies.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('companies', 'code')->whereNull('deleted_at')],
            'rfc' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],

            'storage_commission' => ['nullable', 'numeric', 'min:0'],
            'marketing_commission' => ['nullable', 'numeric', 'min:0'],
            'delayed_payment_commission' => ['nullable', 'numeric', 'min:0'],
            'replacement_contract_commission' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'code' => 'código',
            'rfc' => 'RFC',
            'phone' => 'teléfono',
            'email' => 'correo electrónico',
            'address' => 'dirección',
            'website' => 'sitio web',
            'is_active' => 'estatus',
            'storage_commission' => 'comisión de almacenaje',
            'marketing_commission' => 'comisión de comercialización',
            'delayed_payment_commission' => 'desempeño extemporáneo',
            'replacement_contract_commission' => 'reposición de contrato',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->filled('name') ? trim((string) $this->name) : null,
            'code' => $this->filled('code') ? strtoupper(trim((string) $this->code)) : null,
            'rfc' => $this->filled('rfc') ? strtoupper(trim((string) $this->rfc)) : null,
            'phone' => $this->filled('phone') ? trim((string) $this->phone) : null,
            'email' => $this->filled('email') ? strtolower(trim((string) $this->email)) : null,
            'address' => $this->filled('address') ? trim((string) $this->address) : null,
            'website' => $this->filled('website') ? trim((string) $this->website) : null,
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}