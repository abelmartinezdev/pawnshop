<?php

namespace App\Domain\Pawns\Services;

use App\Domain\Pawns\Models\Pawn;
use App\Domain\Transactions\Services\TransactionService;
use App\Support\OfficeContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PawnService
{
    public function __construct(
        private TransactionService $tx,
        private OfficeContext $office,
    ) {}

    public function verifyLastClosing(): void
    {
        // Aquí metes tu lógica nueva:
        // - validar que no exista cierre hoy para office_id
        // - o validar caja abierta
        // throw ValidationException::withMessages([...]) si está cerrada
    }

    public function liquidate(Pawn $pawn, array $data): int
    {
        if ($pawn->isPaid()) {
            throw ValidationException::withMessages(['pawn' => 'Esta boleta ya fue liquidada.']);
        }

        return DB::transaction(function () use ($pawn, $data) {
            $pawn->markPaid(auth()->id());

            $transactionId = $this->tx->liquidatePawn(
                pawn: $pawn,
                officeId: $this->office->id(),
                payload: $data
            );

            return $transactionId;
        });
    }

    public function countersign(Pawn $pawn, float $payExtra, array $data): int
    {
        if ($payExtra >= $pawn->total) {
            throw ValidationException::withMessages(['pay_extra' => 'El abono es mayor o igual al monto prestado.']);
        }

        return DB::transaction(function () use ($pawn, $payExtra, $data) {
            // “liquidate” en tu legacy solo marca paid_at para cerrar el pawn anterior
            $pawn->markPaid(auth()->id());

            $newPawn = $pawn->makeCountersign($payExtra, auth()->id());

            $transactionId = $this->tx->countersignPawn(
                pawn: $pawn,
                officeId: $this->office->id(),
                payload: $data,
                newPawn: $newPawn
            );

            return $transactionId;
        });
    }

    public function payInterest(Pawn $pawn, float $amountPaid, array $data): int
    {
        $interestTotal = round($pawn->interestToPayMinus1Day()); // lo migramos a método

        if ($amountPaid >= $interestTotal) {
            throw ValidationException::withMessages([
                'payment' => 'La cantidad es igual o mayor al total de intereses. Refrenda y abona a capital.',
            ]);
        }

        return DB::transaction(function () use ($pawn, $amountPaid, $data) {
            return $this->tx->payInterest(
                pawn: $pawn,
                officeId: $this->office->id(),
                amount: $amountPaid,
                payload: $data
            );
        });
    }
}