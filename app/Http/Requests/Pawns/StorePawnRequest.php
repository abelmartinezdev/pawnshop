<?php

namespace App\Http\Requests\Pawns;

use Illuminate\Foundation\Http\FormRequest;

class StorePawnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pawn.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],

            'beneficiary' => ['nullable', 'string', 'max:255'],
            'bag' => ['nullable', 'string', 'max:255'],
            'comments' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.description' => ['required', 'string'],
            'items.*.value' => ['required', 'numeric', 'min:0.01'],

            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*.uid' => ['nullable', 'string', 'max:100'],
            'photos.*.source' => ['nullable', 'string', 'max:50'],
            'photos.*.captured_at' => ['nullable', 'string', 'max:100'],
            'photos.*.data_url' => ['required_with:photos', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_id' => 'cliente',
            'department_id' => 'departamento',
            'beneficiary' => 'beneficiario',
            'bag' => 'bolsa',
            'comments' => 'comentarios',
            'items' => 'artículos',
            'items.*.product_id' => 'producto',
            'items.*.quantity' => 'cantidad',
            'items.*.description' => 'descripción',
            'items.*.value' => 'valor',
        ];
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(fn ($item) => [
                'product_id' => isset($item['product_id']) ? (int) $item['product_id'] : null,
                'quantity' => isset($item['quantity']) ? (float) $item['quantity'] : null,
                'description' => isset($item['description']) ? trim((string) $item['description']) : null,
                'value' => isset($item['value']) ? round((float) $item['value'], 2) : null,
            ])
            ->values()
            ->all();

        $this->merge([
            'customer_id' => $this->filled('customer_id') ? (int) $this->customer_id : null,
            'department_id' => $this->filled('department_id') ? (int) $this->department_id : null,
            'beneficiary' => $this->filled('beneficiary') ? trim((string) $this->beneficiary) : null,
            'bag' => $this->filled('bag') ? trim((string) $this->bag) : null,
            'comments' => $this->filled('comments') ? trim((string) $this->comments) : null,
            'items' => $items,
        ]);
    }
}