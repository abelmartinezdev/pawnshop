<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ShowPawnPayFormAction
{
    public function __invoke(Request $request, Pawn $pawn): Response
    {
        $pawn->load([
            'customer:id,name,state,city,address,phone,mobile,email,rfc,code_id,type_id,inapam_code',
            'office:id,name,serie,cash',
            'items.product:id,code,description,unit,min_price,max_price,department_id',
        ]);

        abort_if($pawn->isCanceled(), 422, 'Este empeño está cancelado.');
        abort_if($pawn->isPaid(), 422, 'Este empeño ya fue liquidado.');
        abort_if($pawn->hasCountersign(), 422, 'Este empeño ya fue refrendado.');

        $interest = $this->interestToPay($pawn);
        $days = $this->daysToPay($pawn);
        $dailyInterest = $this->dailyInterest($pawn);
        $amountToLiquidate = round((float) $pawn->total + $interest, 2);

        return Inertia::render('Pawns/Pay', [
            'pawn' => [
                'id' => $pawn->id,
                'folio' => $pawn->formatted_folio,
                'raw_folio' => $pawn->folio,
                'total' => (float) $pawn->total,
                'created_at' => $this->formatDate($pawn->created_at, 'd/m/Y'),
                'created_at_long' => $this->formatDateLong($pawn->created_at),
                'date_expiration' => $this->formatDate($pawn->date_expiration, 'd/m/Y'),
                'date_auction' => $this->formatDate($pawn->date_auction, 'd/m/Y'),
                'daily_interest_rate' => (float) $pawn->daily_interest_rate,
                'monthly_interest_rate' => (float) $pawn->monthly_interest_rate,
                'iva_rate' => (float) $pawn->iva_rate,
                'inapam_rate' => $pawn->inapam_rate !== null ? (float) $pawn->inapam_rate : null,

                'customer' => $pawn->customer ? [
                    'id' => $pawn->customer->id,
                    'name' => $pawn->customer->name,
                    'phone' => $pawn->customer->display_phone ?? ($pawn->customer->mobile ?: $pawn->customer->phone),
                    'email' => $pawn->customer->email,
                    'rfc' => $pawn->customer->rfc,
                    'code_id' => $pawn->customer->code_id,
                    'type_label' => $pawn->customer->identification_type_label ?? null,
                    'inapam_code' => $pawn->customer->inapam_code,
                ] : null,
            ],

            'payment' => [
                'days_to_pay' => $days,
                'daily_interest' => $dailyInterest,
                'interest_to_pay' => $interest,
                'amount_to_liquidate' => $amountToLiquidate,
                'can_apply_discount' => $request->user()?->can('apply-discount') ?? false,
            ],

            'options' => [
                'transactions' => [
                    [
                        'value' => 'liquidation',
                        'label' => 'Desempeñar',
                        'description' => 'El cliente liquida capital e interés.',
                    ],
                    [
                        'value' => 'countersign',
                        'label' => 'Refrendo',
                        'description' => 'El cliente paga interés y renueva el contrato.',
                    ],
                    [
                        'value' => 'interest_payment',
                        'label' => 'Abono a interés',
                        'description' => 'El cliente abona una parte del interés.',
                    ],
                ],
                'payment_types' => [
                    ['value' => 'cash', 'label' => 'Efectivo'],
                    ['value' => 'card', 'label' => 'Tarjeta'],
                    ['value' => 'transfer', 'label' => 'Transferencia'],
                ],
            ],

            'urls' => [
                'show' => route('pawns.show', $pawn->id),
                'pay' => route('pawns.pay', $pawn->id),
            ],
        ]);
    }

    private function interestToPay(Pawn $pawn): float
    {
        try {
            return round((float) $pawn->getInterest2payLess1day(), 2);
        } catch (Throwable) {
            $days = $this->daysToPay($pawn);
            $dailyRate = (float) $pawn->daily_interest_rate / 100;
            $interest = (float) $pawn->total * $dailyRate * $days;

            $iva = (float) $pawn->iva_rate;
            $ivaFactor = $iva > 1 ? $iva / 100 : $iva;

            $interest += $interest * $ivaFactor;

            if ($pawn->inapam_rate) {
                $interest -= $interest * (float) $pawn->inapam_rate;
            }

            return round(max($interest, 0), 2);
        }
    }

    private function dailyInterest(Pawn $pawn): float
    {
        try {
            return round((float) $pawn->getDailyInterest(), 2);
        } catch (Throwable) {
            $dailyRate = (float) $pawn->daily_interest_rate / 100;
            $interest = (float) $pawn->total * $dailyRate;

            $iva = (float) $pawn->iva_rate;
            $ivaFactor = $iva > 1 ? $iva / 100 : $iva;

            $interest += $interest * $ivaFactor;

            return round(max($interest, 0), 2);
        }
    }

    private function daysToPay(Pawn $pawn): int
    {
        try {
            return max((int) $pawn->getDays2PayMinus1Attribute(), 1);
        } catch (Throwable) {
            return max(Carbon::parse($pawn->created_at)->diffInDays(now()), 1);
        }
    }

    private function formatDate(mixed $value, string $format = 'd/m/Y H:i'): ?string
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

    private function formatDateLong(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)
                ->locale('es')
                ->translatedFormat('l j \d\e F Y');
        } catch (Throwable) {
            return (string) $value;
        }
    }
}