<?php

namespace App\Http\Requests\Quotations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContinueQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->can('pawn.manage');
    }

    public function rules(): array
    {
        return [
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')
                    ->where(
                        fn ($query) => $query
                            ->where('is_active', true)
                            ->whereNull('deleted_at')
                    ),
            ],

            /*
             * El importe es libre. No se valida contra el mínimo
             * o máximo sugerido.
             */
            'total_import' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999999.99',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')
                    ->where(
                        fn ($query) => $query
                            ->where('is_active', true)
                            ->whereNull('deleted_at')
                    ),
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999.999',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.required' =>
                'No se identificó el departamento de la cotización.',

            'department_id.exists' =>
                'El departamento seleccionado no está disponible.',

            'total_import.required' =>
                'Captura el importe que deseas prestar.',

            'total_import.numeric' =>
                'El préstamo debe ser un importe válido.',

            'total_import.gt' =>
                'El préstamo debe ser mayor que cero.',

            'items.required' =>
                'Captura el peso de al menos una prenda.',

            'items.min' =>
                'Captura el peso de al menos una prenda.',

            'items.*.product_id.required' =>
                'Hay un producto sin identificar.',

            'items.*.product_id.exists' =>
                'Uno de los productos ya no está disponible.',

            'items.*.product_id.distinct' =>
                'No se puede repetir el mismo producto.',

            'items.*.quantity.required' =>
                'Captura los gramos del producto.',

            'items.*.quantity.numeric' =>
                'Los gramos deben ser un número válido.',

            'items.*.quantity.gt' =>
                'Los gramos deben ser mayores que cero.',
        ];
    }
}