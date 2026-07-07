<?php

namespace App\Http\Requests\Pawns;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePawnDateExpirationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pawn.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'date_expiration' => ['required', 'date_format:Y-m-d'],
            'date_auction' => ['required', 'date_format:Y-m-d', 'after_or_equal:date_expiration'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date_expiration' => 'fecha de expiración',
            'date_auction' => 'fecha de remate',
            'comments' => 'comentarios',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'date_expiration' => $this->filled('date_expiration')
                ? trim((string) $this->date_expiration)
                : null,

            'date_auction' => $this->filled('date_auction')
                ? trim((string) $this->date_auction)
                : null,

            'comments' => $this->filled('comments')
                ? trim((string) $this->comments)
                : null,
        ]);
    }
}