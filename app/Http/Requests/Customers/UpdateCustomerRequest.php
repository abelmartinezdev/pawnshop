<?php

namespace App\Http\Requests\Customers;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('pawn.manage') || $this->user()?->can('cash.manage')) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'rfc' => ['nullable', 'string', 'min:10', 'max:13'],
            'code_id' => ['nullable', 'string', 'max:100'],
            'type_id' => ['nullable', 'integer', Rule::in(array_keys(Customer::IDENTIFICATION_TYPES))],
            'inapam_code' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'state' => 'estado',
            'city' => 'ciudad',
            'address' => 'dirección',
            'phone' => 'teléfono',
            'mobile' => 'celular',
            'email' => 'correo electrónico',
            'rfc' => 'RFC',
            'code_id' => 'número de identificación',
            'type_id' => 'tipo de identificación',
            'inapam_code' => 'clave INAPAM',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->filled('name') ? trim((string) $this->name) : null,
            'state' => $this->filled('state') ? trim((string) $this->state) : null,
            'city' => $this->filled('city') ? trim((string) $this->city) : null,
            'address' => $this->filled('address') ? trim((string) $this->address) : null,
            'phone' => $this->filled('phone') ? trim((string) $this->phone) : null,
            'mobile' => $this->filled('mobile') ? trim((string) $this->mobile) : null,
            'email' => $this->filled('email') ? mb_strtolower(trim((string) $this->email)) : null,
            'rfc' => $this->filled('rfc') ? mb_strtoupper(trim((string) $this->rfc)) : null,
            'code_id' => $this->filled('code_id') ? trim((string) $this->code_id) : null,
            'type_id' => $this->filled('type_id') ? (int) $this->type_id : null,
            'inapam_code' => $this->filled('inapam_code') ? trim((string) $this->inapam_code) : null,
        ]);
    }
}