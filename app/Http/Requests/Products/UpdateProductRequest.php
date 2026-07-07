<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pawn.manage') ?? false;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'code')
                    ->ignore($productId)
                    ->whereNull('deleted_at'),
            ],
            'description' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'min_price' => ['required', 'numeric', 'min:0', 'max:999999999.999'],
            'max_price' => ['required', 'numeric', 'min:0', 'max:999999999.999', 'gte:min_price'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'department_id' => 'departamento',
            'code' => 'código',
            'description' => 'descripción',
            'unit' => 'unidad',
            'min_price' => 'precio mínimo',
            'max_price' => 'precio máximo',
            'is_active' => 'estatus',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'department_id' => $this->filled('department_id') ? (int) $this->department_id : null,
            'code' => $this->filled('code') ? mb_strtoupper(trim((string) $this->code)) : null,
            'description' => $this->filled('description') ? trim((string) $this->description) : null,
            'unit' => $this->filled('unit') ? mb_strtoupper(trim((string) $this->unit)) : 'PIEZA',
            'min_price' => $this->filled('min_price') ? round((float) $this->min_price, 3) : 0,
            'max_price' => $this->filled('max_price') ? round((float) $this->max_price, 3) : 0,
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}