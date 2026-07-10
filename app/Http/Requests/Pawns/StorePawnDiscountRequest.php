<?php

namespace App\Http\Requests\Pawns;

use Illuminate\Foundation\Http\FormRequest;

class StorePawnDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()?->can('apply-discount');
    }

    public function rules(): array
    {
        return [
            'days' => [
                'required',
                'integer',
                'min:1',
            ],
            'comments' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'days' => 'días a descontar',
            'comments' => 'comentarios',
        ];
    }

    public function messages(): array
    {
        return [
            'days.required' => 'Captura los días que deseas descontar.',
            'days.integer' => 'Los días deben ser un número entero.',
            'days.min' => 'Debes descontar al menos 1 día.',
        ];
    }
}