<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ShowPawnAnticipatedDateAction
{
    public function __invoke(Pawn $pawn): Response|RedirectResponse
    {
        $pawn->load([
            'customer:id,name,phone,mobile,email,rfc,code_id',
            'office:id,name,serie,cash',
            'items.product:id,code,description,unit',
        ]);

        if (! $this->canUseAnticipatedDate($pawn)) {
            return redirect()
                ->route('pawns.show', $pawn->id)
                ->with('error', 'No se puede calcular fecha anticipada para este empeño.');
        }

        $paymentPreview = $this->paymentPreview($pawn);

        return Inertia::render('Pawns/AnticipatedDate', [
            'pawn' => [
                'id' => $pawn->id,
                'folio' => $this->formattedFolio($pawn),
                'raw_folio' => $pawn->folio,

                'total' => (float) $pawn->total,
                'estimated_value' => (float) $pawn->estimated_value,
                'loan_value' => (float) $pawn->loan_value,
                'pay_extra' => (float) ($pawn->pay_extra ?? 0),

                'amount_to_liquidate' => $paymentPreview['amount_to_liquidate'],
                'interest_to_pay' => $paymentPreview['interest_to_pay'],
                'iva_to_pay' => $paymentPreview['iva_to_pay'],
                'daily_interest' => $paymentPreview['daily_interest'],
                'days_to_pay' => $paymentPreview['days_to_pay'],
                'paid_amount' => $paymentPreview['paid_amount'],

                'loan_rate' => (float) $pawn->loan_rate,
                'daily_interest_rate' => (float) $pawn->daily_interest_rate,
                'monthly_interest_rate' => (float) $pawn->monthly_interest_rate,
                'iva_rate' => (float) $pawn->iva_rate,
                'inapam_rate' => $pawn->inapam_rate !== null ? (float) $pawn->inapam_rate : 0,

                'term' => (int) $pawn->term,
                'auction' => (int) $pawn->auction,

                'created_at' => $this->formatDate($pawn->created_at),
                'created_at_iso' => $this->formatDate($pawn->created_at, 'Y-m-d'),
                'date_expiration' => $this->formatDate($pawn->date_expiration, 'd/m/Y'),
                'date_auction' => $this->formatDate($pawn->date_auction, 'd/m/Y'),

                'minimum_days' => $this->minimumDays($pawn),

                'customer' => $pawn->customer ? [
                    'id' => $pawn->customer->id,
                    'name' => $pawn->customer->name,
                    'phone' => $pawn->customer->display_phone ?? ($pawn->customer->mobile ?: $pawn->customer->phone),
                    'email' => $pawn->customer->email,
                    'rfc' => $pawn->customer->rfc,
                    'code_id' => $pawn->customer->code_id,
                ] : null,

                'office' => $pawn->office ? [
                    'id' => $pawn->office->id,
                    'name' => $pawn->office->name,
                    'serie' => $pawn->office->serie,
                    'cash' => (float) $pawn->office->cash,
                ] : null,

                'items' => $pawn->items
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'quantity' => (float) $item->quantity,
                        'description' => $item->description,
                        'value' => (float) $item->value,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'code' => $item->product->code,
                            'description' => $item->product->description,
                            'unit' => $item->product->unit,
                        ] : null,
                    ])
                    ->values(),
            ],

            'date' => now()->toDateString(),
            'total' => $paymentPreview['amount_to_liquidate'],

            'urls' => [
                'show' => Route::has('pawns.show') ? route('pawns.show', $pawn->id) : null,
                'index' => Route::has('pawns.index') ? route('pawns.index') : null,
                'pay' => Route::has('pawns.payForm') ? route('pawns.payForm', $pawn->id) : null,
                'print_ticket' => Route::has('pawns.print.anticipated-date')
                ? route('pawns.print.anticipated-date', $pawn->id)
                : null,
            ],
        ]);
    }

    private function canUseAnticipatedDate(Pawn $pawn): bool
    {
        return $pawn->auction_at === null
            && $pawn->paid_at === null
            && $pawn->canceled_at === null
            && ! $pawn->hasCountersign();
    }

    private function paymentPreview(Pawn $pawn): array
    {
        $total = (float) $pawn->total;

        $days = max(
            $this->minimumDays($pawn),
            Carbon::parse($pawn->created_at?->toDateString() ?: now()->toDateString())
                ->diffInDays(Carbon::parse(now()->toDateString()))
        );

        $dailyRate = (float) $pawn->daily_interest_rate / 100;
        $ivaRate = (float) $pawn->iva_rate;
        $ivaFactor = $ivaRate > 1 ? $ivaRate / 100 : $ivaRate;

        $inapamRate = (float) ($pawn->inapam_rate ?? 0);
        $inapamFactor = $inapamRate > 1 ? $inapamRate / 100 : $inapamRate;

        $paidAmount = (float) $pawn->transactionsPaidAmount()->sum('amount');

        $dailyInterestWithoutIva = round($total * $dailyRate, 2);
        $interestWithoutIva = round($dailyInterestWithoutIva * $days, 2);
        $iva = round($interestWithoutIva * $ivaFactor, 2);

        $interestWithIva = round($interestWithoutIva + $iva, 2);
        $inapamDiscount = $inapamFactor > 0 ? round($interestWithIva * $inapamFactor, 2) : 0;

        $interestToPay = round(max($interestWithIva - $inapamDiscount - $paidAmount, 0), 2);

        return [
            'days_to_pay' => $days,
            'daily_interest' => $dailyInterestWithoutIva,
            'interest_to_pay' => $interestToPay,
            'iva_to_pay' => $iva,
            'paid_amount' => $paidAmount,
            'amount_to_liquidate' => round($total + $interestToPay, 2),
        ];
    }

    private function minimumDays(Pawn $pawn): int
    {
        $firstProductId = $pawn->items->first()?->product_id;

        if ((int) $firstProductId === 34) {
            return 15;
        }

        return 5;
    }

    private function formattedFolio(Pawn $pawn): string
    {
        $serie = $pawn->office?->serie ?: 'A';

        return $serie . str_pad((string) $pawn->folio, 6, '0', STR_PAD_LEFT);
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
}