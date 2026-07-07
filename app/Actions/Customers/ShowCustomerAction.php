<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use App\Models\Pawn;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ShowCustomerAction
{
    public function __invoke(int $id): Response
    {
        $customer = Customer::query()
            ->withTrashed()
            ->withCount('pawns')
            ->findOrFail($id);

        $pawns = Pawn::query()
            ->where('customer_id', $customer->id)
            ->latest('id')
            ->take(12)
            ->get();

        $pawnIds = $pawns->pluck('id')->values();

        $movements = Transaction::query()
            ->with([
                'pawn:id,folio,customer_id',
                'user:id,name,email',
            ])
            ->whereIn('pawn_id', $pawnIds)
            ->latest('id')
            ->take(15)
            ->get();

        $totalPawned = (float) Pawn::query()
            ->where('customer_id', $customer->id)
            ->sum('total');

        $activePawns = Pawn::query()
            ->where('customer_id', $customer->id)
            ->whereNull('paid_at')
            ->whereNull('canceled_at')
            ->count();

        return Inertia::render('Customers/Show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'state' => $customer->state,
                'city' => $customer->city,
                'address' => $customer->address,
                'full_address' => $customer->full_address,
                'phone' => $customer->phone,
                'mobile' => $customer->mobile,
                'display_phone' => $customer->display_phone,
                'email' => $customer->email,
                'rfc' => $customer->rfc,
                'code_id' => $customer->code_id,
                'type_id' => $customer->type_id,
                'type_label' => $customer->identification_type_label,
                'inapam_code' => $customer->inapam_code,
                'pawns_count' => (int) $customer->pawns_count,
                'active_pawns_count' => (int) $activePawns,
                'total_pawned' => $totalPawned,
                'created_at' => $this->formatDate($customer->created_at),
                'updated_at' => $this->formatDate($customer->updated_at),
                'deleted_at' => $this->formatDate($customer->deleted_at),
                'is_deleted' => $customer->trashed(),

                'pawns' => $pawns
                    ->map(fn (Pawn $pawn) => [
                        'id' => $pawn->id,
                        'folio' => $pawn->folio,
                        'total' => (float) $pawn->total,
                        'date' => $this->formatDate($pawn->date, 'd/m/Y'),
                        'date_expiration' => $this->formatDate($pawn->date_expiration, 'd/m/Y'),
                        'paid_at' => $this->formatDate($pawn->paid_at),
                        'canceled_at' => $this->formatDate($pawn->canceled_at),
                        'created_at' => $this->formatDate($pawn->created_at),
                        'status' => $this->pawnStatus($pawn),
                        'status_label' => $this->pawnStatusLabel($pawn),
                        'show_url' => Route::has('pawns.show')
                            ? route('pawns.show', $pawn->id)
                            : null,
                    ])
                    ->values(),

                'movements' => $movements
                    ->map(fn (Transaction $transaction) => [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'type_label' => $this->transactionTypeLabel($transaction->type),
                        'amount' => (float) $transaction->amount,
                        'balance' => (float) $transaction->balance,
                        'payment_type' => $transaction->payment_type,
                        'comments' => $transaction->comments,
                        'created_at' => $this->formatDate($transaction->created_at),
                        'canceled_at' => $this->formatDate($transaction->canceled_at),
                        'is_cancelled' => $transaction->canceled_at !== null,
                        'pawn' => $transaction->pawn ? [
                            'id' => $transaction->pawn->id,
                            'folio' => $transaction->pawn->folio,
                            'show_url' => Route::has('pawns.show')
                                ? route('pawns.show', $transaction->pawn->id)
                                : null,
                        ] : null,
                        'user' => $transaction->user ? [
                            'id' => $transaction->user->id,
                            'name' => $transaction->user->name,
                        ] : null,
                    ])
                    ->values(),
            ],

            'urls' => [
                'new_pawn' => Route::has('pawns.create')
                    ? route('pawns.create', ['customer_id' => $customer->id])
                    : null,
            ],
        ]);
    }

    private function pawnStatus(Pawn $pawn): string
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

        return 'active';
    }

    private function pawnStatusLabel(Pawn $pawn): string
    {
        return match ($this->pawnStatus($pawn)) {
            'cancelled' => 'Cancelado',
            'paid' => 'Liquidado',
            'expired' => 'Vencido',
            default => 'Activo',
        };
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