<?php

namespace App\Actions\Pawns;

use App\Models\Pawn;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ShowPawnAction
{
    public function __invoke(Pawn $pawn): Response
    {
        $pawn->load([
            'customer:id,name,state,city,address,phone,mobile,email,rfc,code_id,type_id,inapam_code',
            'office:id,name,serie,cash',
            'creator:id,name,email',
            'canceledBy:id,name,email',
            'previousPawn:id,folio,office_id',
            'previousPawn.office:id,serie,name',
            'items.product:id,code,description,unit,min_price,max_price,department_id',
        ]);

        $transactions = Transaction::query()
            ->with('user:id,name,email')
            ->where('pawn_id', $pawn->id)
            ->latest('id')
            ->get();

        $paymentPreview = $this->paymentPreview($pawn);

        return Inertia::render('Pawns/Show', [
            'pawn' => [
                'id' => $pawn->id,
                'folio' => $this->formattedFolio($pawn),
                'raw_folio' => $pawn->folio,
                'status' => $this->status($pawn),
                'status_label' => $this->statusLabel($pawn),

                'total' => (float) $pawn->total,
                'estimated_value' => (float) $pawn->estimated_value,
                'loan_value' => (float) $pawn->loan_value,
                'pay_extra' => (float) ($pawn->pay_extra ?? 0),

                'amount_to_liquidate' => $paymentPreview['amount_to_liquidate'],
                'interest_to_pay' => $paymentPreview['interest_to_pay'],
                'iva_to_pay' => $paymentPreview['iva_to_pay'],
                'daily_interest' => $paymentPreview['daily_interest'],
                'days_to_pay' => $paymentPreview['days_to_pay'],

                'loan_rate' => (float) $pawn->loan_rate,
                'daily_interest_rate' => (float) $pawn->daily_interest_rate,
                'monthly_interest_rate' => (float) $pawn->monthly_interest_rate,
                'iva_rate' => (float) $pawn->iva_rate,
                'inapam_rate' => $pawn->inapam_rate !== null ? (float) $pawn->inapam_rate : null,

                'term' => (int) $pawn->term,
                'auction' => (int) $pawn->auction,

                'date_expiration' => $this->formatDate($pawn->date_expiration, 'd/m/Y'),
                'date_auction' => $this->formatDate($pawn->date_auction, 'd/m/Y'),
                'date_settlement' => $this->formatDate($pawn->date_settlement, 'd/m/Y'),
                'created_at' => $this->formatDate($pawn->created_at),
                'updated_at' => $this->formatDate($pawn->updated_at),
                'paid_at' => $this->formatDate($pawn->paid_at),
                'canceled_at' => $this->formatDate($pawn->canceled_at),

                'is_paid' => $pawn->paid_at !== null,
                'is_cancelled' => $pawn->canceled_at !== null,
                'has_photos' => count($this->photos($pawn)) > 0,

                'beneficiary' => $pawn->beneficiary,
                'bag' => $pawn->bag,
                'comments' => $pawn->comments,
                'photos' => $this->photos($pawn),

                'customer' => $pawn->customer ? [
                    'id' => $pawn->customer->id,
                    'name' => $pawn->customer->name,
                    'state' => $pawn->customer->state,
                    'city' => $pawn->customer->city,
                    'address' => $pawn->customer->address,
                    'full_address' => $pawn->customer->full_address ?? collect([
                        $pawn->customer->address,
                        $pawn->customer->city,
                        $pawn->customer->state,
                    ])->filter()->join(', '),
                    'phone' => $pawn->customer->display_phone ?? ($pawn->customer->mobile ?: $pawn->customer->phone),
                    'email' => $pawn->customer->email,
                    'rfc' => $pawn->customer->rfc,
                    'code_id' => $pawn->customer->code_id,
                    'type_label' => $pawn->customer->identification_type_label ?? null,
                    'inapam_code' => $pawn->customer->inapam_code,
                ] : null,

                'office' => $pawn->office ? [
                    'id' => $pawn->office->id,
                    'name' => $pawn->office->name,
                    'serie' => $pawn->office->serie,
                    'cash' => (float) $pawn->office->cash,
                ] : null,

                'creator' => $pawn->creator ? [
                    'id' => $pawn->creator->id,
                    'name' => $pawn->creator->name,
                ] : null,

                'canceled_by' => $pawn->canceledBy ? [
                    'id' => $pawn->canceledBy->id,
                    'name' => $pawn->canceledBy->name,
                ] : null,

                'previous_pawn' => $pawn->previousPawn ? [
                    'id' => $pawn->previousPawn->id,
                    'folio' => $this->formattedFolio($pawn->previousPawn),
                ] : null,

                'items' => $pawn->items
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'quantity' => (float) $item->quantity,
                        'description' => $item->description,
                        'value' => (float) $item->value,
                        'created_at' => $this->formatDate($item->created_at),
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'code' => $item->product->code,
                            'description' => $item->product->description,
                            'unit' => $item->product->unit,
                            'min_price' => (float) $item->product->min_price,
                            'max_price' => (float) $item->product->max_price,
                        ] : null,
                    ])
                    ->values(),

                'transactions' => $transactions
                    ->map(fn (Transaction $transaction) => [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'type_label' => $this->transactionTypeLabel($transaction->type),
                        'amount' => (float) $transaction->amount,
                        'balance' => (float) $transaction->balance,
                        'payment_type' => $transaction->payment_type,
                        'payment_type_label' => $this->paymentTypeLabel($transaction->payment_type),
                        'comments' => $transaction->comments,
                        'comments_cancel' => $transaction->comments_cancel,
                        'is_cancelled' => $transaction->canceled_at !== null,
                        'canceled_at' => $this->formatDate($transaction->canceled_at),
                        'created_at' => $this->formatDate($transaction->created_at),
                        'user' => $transaction->user ? [
                            'id' => $transaction->user->id,
                            'name' => $transaction->user->name,
                        ] : null,
                    ])
                    ->values(),

                'payment_options' => $this->paymentOptions($pawn),
            ],

            'urls' => [
                'index' => Route::has('pawns.index') ? route('pawns.index') : null,
                'pay' => Route::has('pawns.payForm') ? route('pawns.payForm', $pawn->id) : null,
                'customer' => $pawn->customer && Route::has('customers.show')
                    ? route('customers.show', $pawn->customer->id)
                    : null,
                'previous_pawn' => $pawn->previousPawn && Route::has('pawns.show')
                    ? route('pawns.show', $pawn->previousPawn->id)
                    : null,
                'print_countersign' => Route::has('pawns.print.countersign')
                    ? route('pawns.print.countersign', $pawn->id)
                    : null,
                'date_expiration' => Route::has('pawns.date-expiration.edit')
                    ? route('pawns.date-expiration.edit', $pawn->id)
                    : null,
            ],
        ]);
    }

    private function formattedFolio(Pawn $pawn): string
    {
        $serie = $pawn->office?->serie ?: 'A';

        return $serie . str_pad((string) $pawn->folio, 6, '0', STR_PAD_LEFT);
    }

    private function status(Pawn $pawn): string
    {
        if ($pawn->canceled_at) {
            return 'cancelled';
        }

        if ($pawn->paid_at) {
            return 'paid';
        }

        if ($pawn->date_expiration && Carbon::parse($pawn->date_expiration)->isPast()) {
            return 'expired';
        }

        return 'pending';
    }

    private function statusLabel(Pawn $pawn): string
    {
        return match ($this->status($pawn)) {
            'cancelled' => 'Cancelado',
            'paid' => 'Liquidado',
            'expired' => 'Vencido',
            default => 'Pendiente de pago',
        };
    }

    private function photos(Pawn $pawn): array
    {
        if (! $pawn->photos) {
            return [];
        }

        if (is_array($pawn->photos)) {
            return collect($pawn->photos)->filter()->values()->all();
        }

        $value = trim((string) $pawn->photos);

        if ($value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return collect($decoded)->filter()->values()->all();
        }

        return collect(preg_split('/[;,|]/', $value))
            ->map(fn ($photo) => trim((string) $photo))
            ->filter()
            ->values()
            ->all();
    }

    private function paymentPreview(Pawn $pawn): array
    {
        $total = (float) $pawn->total;
        $days = max(1, Carbon::parse($pawn->created_at)->diffInDays(now()) + 1);
        $dailyRate = (float) $pawn->daily_interest_rate / 100;
        $ivaRate = (float) $pawn->iva_rate;
        $ivaFactor = $ivaRate > 1 ? $ivaRate / 100 : $ivaRate;

        $dailyInterest = round($total * $dailyRate, 2);
        $interest = round($dailyInterest * $days, 2);
        $iva = round($interest * $ivaFactor, 2);

        return [
            'days_to_pay' => $days,
            'daily_interest' => $dailyInterest,
            'interest_to_pay' => $interest,
            'iva_to_pay' => $iva,
            'amount_to_liquidate' => round($total + $interest + $iva, 2),
        ];
    }

    private function paymentOptions(Pawn $pawn): array
    {
        $total = (float) $pawn->total;
        $term = max(1, (int) $pawn->term);
        $auction = max(1, (int) $pawn->auction);
        $dailyRate = (float) $pawn->daily_interest_rate / 100;
        $ivaRate = (float) $pawn->iva_rate;
        $ivaFactor = $ivaRate > 1 ? $ivaRate / 100 : $ivaRate;

        return collect([
            ['number' => 1, 'days' => $term],
            ['number' => 2, 'days' => $term + $auction],
        ])
            ->map(function (array $row) use ($total, $dailyRate, $ivaFactor) {
                $interest = round($total * $dailyRate * $row['days'], 2);
                $iva = round($interest * $ivaFactor, 2);
                $refinance = round($interest + $iva, 2);

                return [
                    'number' => $row['number'],
                    'principal' => $total,
                    'interest' => $interest,
                    'storage' => 0,
                    'iva' => $iva,
                    'refinance_total' => $refinance,
                    'liquidate_total' => round($total + $refinance, 2),
                    'date' => now()->addDays($row['days'])->format('d/m/Y'),
                ];
            })
            ->values()
            ->all();
    }

    private function transactionTypeLabel(?string $type): string
    {
        return match ($type) {
            'pawn' => 'Empeño',
            'countersign', 'refrendo', 'refinance' => 'Refrendo',
            'liquidation' => 'Liquidación',
            'payment', 'payment_to_interest', 'interest_payment', 'abono_interes' => 'Abono',
            'manual_income' => 'Ingreso manual',
            'manual_expense' => 'Gasto manual',
            'sale' => 'Venta',
            'aside' => 'Apartado',
            'aside_payment' => 'Abono a apartado',
            'adjustment' => 'Ajuste',
            default => $type ? ucfirst(str_replace('_', ' ', $type)) : 'Movimiento',
        };
    }

    private function paymentTypeLabel(?string $type): string
    {
        return match ($type) {
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
            default => $type ? ucfirst($type) : 'No especificado',
        };
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