<?php

namespace App\Http\Requests\Pawns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelPawnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'cancellation_type' => [
                'required',
                'string',
                Rule::in(array_keys($this->cancellationTypes())),
            ],
            'number_investigation' => [
                'nullable',
                'string',
                'max:100',
                'required_if:cancellation_type,investigation',
            ],
            'comments_cancel' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'cancellation_type' => 'motivo de cancelación',
            'number_investigation' => 'número de investigación',
            'comments_cancel' => 'comentarios de cancelación',
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_type.required' => 'Selecciona el motivo de cancelación.',
            'cancellation_type.in' => 'El motivo de cancelación seleccionado no es válido.',
            'number_investigation.required_if' => 'Captura el número de investigación.',
        ];
    }

    private function cancellationTypes(): array
    {
        $types = config('core.cancellation_types', []);

        if (is_array($types) && count($types) > 0) {
            return $types;
        }

        return [
            'capture_error' => 'Error de captura',
            'client_request' => 'Solicitud del cliente',
            'investigation' => 'Investigación',
            'other' => 'Otro',
        ];
    }
}