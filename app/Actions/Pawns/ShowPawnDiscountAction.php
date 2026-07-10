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

class ShowPawnDiscountAction
{
    public function __invoke(Pawn $pawn): Response|RedirectResponse
    {
        $pawn->load([
            'customer:id,name,phone,mobile,email,rfc,code_id',
            'office:id,name,serie,cash',
            'items.product:id,code,description,unit',
        ]);

        // if (! auth()->user()?->can('apply-discount')) {
        //     abort(403, 'No tienes permiso para aplicar descuentos.');
        // }

        // if (! $this->canApplyDiscount($pawn)) {
        //     return redirect()
        //         ->route('pawns.show', $pawn->id)
        //         ->with('error', 'No se puede aplicar descuento a este empeño.');
        // }

        $summary = $this->summary($pawn);

        return Inertia::render('Pawns/ApplyDiscount', [
            'pawn' => [
                'id' => $pawn->id,
                'folio' => $this->formattedFolio($pawn),
                'raw_folio' => $pawn->folio,

                'total' => (float) $pawn->total,
                'created_at' => $this->formatDate($pawn->created_at),
                'date_expiration' => $this->formatDate($pawn->date_expiration, 'd/m/Y'),
                'date_auction' => $this->formatDate($pawn->date_auction, 'd/m/Y'),

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

            'summary' => $summary,

            'urls' => [
                'show' => Route::has('pawns.show') ? route('pawns.show', $pawn->id) : null,
                'store' => Route::has('pawns.apply-discount.store')
                    ? route('pawns.apply-discount.store', $pawn->id)
                    : null,
            ],
        ]);
    }

    private function canApplyDiscount(Pawn $pawn): bool
    {
        return $pawn->auction_at === null
            && $pawn->paid_at === null
            && $pawn->canceled_at === null
            && ! $pawn->hasCountersign();
    }

    private function summary(Pawn $pawn): array
    {
        $days = (int) $pawn->days2payminus1;
        $discountedDays = method_exists($pawn, 'interestDiscountDays')
            ? $pawn->interestDiscountDays()
            : 0;

        $maxDiscountDays = max($days - $discountedDays, 0);

        $dailyInterestWithIva = round((float) $pawn->getDailyInterest(true), 2);
        $dailyInterestWithoutIva = round((float) $pawn->getDailyInterest(false), 2);

        $interestBeforeDiscount = round($dailyInterestWithIva * $days, 2);
        $interestDiscountAmount = method_exists($pawn, 'interestDiscountAmount')
            ? round($pawn->interestDiscountAmount(true), 2)
            : 0;

        $interestCurrent = round(max($interestBeforeDiscount - $interestDiscountAmount - (float) $pawn->paidAmount(), 0), 2);

        return [
            'days_to_pay' => $days,
            'discounted_days' => $discountedDays,
            'max_discount_days' => $maxDiscountDays,

            'daily_interest' => $dailyInterestWithIva,
            'daily_interest_without_iva' => $dailyInterestWithoutIva,

            'interest_before_discount' => $interestBeforeDiscount,
            'interest_discount_amount' => $interestDiscountAmount,
            'interest_current' => $interestCurrent,

            'paid_amount' => round((float) $pawn->paidAmount(), 2),

            'amount_current' => round((float) $pawn->total + $interestCurrent, 2),
        ];
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