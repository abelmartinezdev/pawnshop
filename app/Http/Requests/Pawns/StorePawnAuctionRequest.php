<?php

namespace App\Http\Requests\Pawns;

use Illuminate\Foundation\Http\FormRequest;

class StorePawnAuctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->can('pawn.manage');
    }

    public function rules(): array
    {
        return [
            'confirmation' => [
                'required',
                'accepted',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'confirmation.required' => 'Debes confirmar el pase a remate.',
            'confirmation.accepted' => 'Debes confirmar el pase a remate.',
        ];
    }
}