<?php

namespace App\Http\Requests\Closures;

use Illuminate\Foundation\Http\FormRequest;

class StoreClosureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cash.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'counted_cash' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'counted_cash' => 'efectivo contado',
            'comments' => 'comentarios',
        ];
    }
}