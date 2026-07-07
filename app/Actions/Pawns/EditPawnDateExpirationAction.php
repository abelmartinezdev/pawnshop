<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class EditPawnDateExpirationAction
{
    public function __invoke(Pawn $pawn): Response
    {
        $officeId = session('office_id') ?: auth()->user()?->office_id;
        $companyId = session('company_id') ?: auth()->user()?->company_id;

        abort_unless($officeId, 404, 'No hay una sucursal activa.');
        abort_unless($companyId, 404, 'No hay una empresa activa.');
        abort_if((int) $pawn->office_id !== (int) $officeId, 403, 'El empeño no pertenece a la sucursal activa.');
        abort_if((int) $pawn->company_id !== (int) $companyId, 403, 'El empeño no pertenece a la empresa activa.');
        abort_if($pawn->isCanceled(), 422, 'Este empeño está cancelado.');
        abort_if($pawn->isPaid(), 422, 'Este empeño ya fue liquidado.');

        $pawn->load([
            'customer:id,name,phone,mobile,email,rfc,code_id,type_id',
            'office:id,name,serie',
        ]);

        return Inertia::render('Pawns/DateExpiration', [
            'pawn' => [
                'id' => $pawn->id,
                'folio' => $pawn->formatted_folio,
                'raw_folio' => $pawn->folio,

                'date_expiration' => $this->formatDate($pawn->date_expiration, 'd/m/Y'),
                'date_auction' => $this->formatDate($pawn->date_auction, 'd/m/Y'),

                'date_expiration_input' => $this->formatDate($pawn->date_expiration, 'Y-m-d'),
                'date_auction_input' => $this->formatDate($pawn->date_auction, 'Y-m-d'),

                'customer' => $pawn->customer ? [
                    'id' => $pawn->customer->id,
                    'name' => $pawn->customer->name,
                    'phone' => $pawn->customer->display_phone ?? ($pawn->customer->mobile ?: $pawn->customer->phone),
                    'rfc' => $pawn->customer->rfc,
                    'code_id' => $pawn->customer->code_id,
                    'type_label' => $pawn->customer->identification_type_label ?? null,
                ] : null,

                'office' => $pawn->office ? [
                    'id' => $pawn->office->id,
                    'name' => $pawn->office->name,
                    'serie' => $pawn->office->serie,
                ] : null,
            ],

            'urls' => [
                'show' => route('pawns.show', $pawn->id),
                'update' => route('pawns.date-expiration.update', $pawn->id),
            ],
        ]);
    }

    private function formatDate(mixed $value, string $format): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format($format);
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (Throwable) {
            return (string) $value;
        }
    }
}