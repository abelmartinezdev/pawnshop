<?php

namespace App\Actions\Pawns;

use App\Http\Requests\Pawns\CancelPawnRequest;
use App\Models\Office;
use App\Models\Pawn;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class CancelPawnAction
{
    public function __invoke(CancelPawnRequest $request, Pawn $pawn): RedirectResponse
    {
        if (! $pawn->mayBeCanceled()) {
            return redirect()
                ->route('pawns.show', $pawn->id)
                ->with('error', 'No se puede cancelar este empeño. Puede estar pagado, refrendado, rematado, cancelado o tener movimientos posteriores.');
        }

        try {
            DB::transaction(function () use ($request, $pawn) {
                $pawn = Pawn::query()
                    ->with(['office', 'transactions'])
                    ->lockForUpdate()
                    ->findOrFail($pawn->id);

                if (! $pawn->mayBeCanceled()) {
                    throw ValidationException::withMessages([
                        'pawn' => 'No se puede cancelar este empeño.',
                    ]);
                }

                $office = Office::query()
                    ->lockForUpdate()
                    ->findOrFail($pawn->office_id);

                $mainTransaction = $this->mainPawnTransaction($pawn);

                if (! $mainTransaction) {
                    throw ValidationException::withMessages([
                        'pawn' => 'No se encontró la transacción original del empeño. No se puede revertir caja de forma segura.',
                    ]);
                }

                $amountToReturn = $this->amountToReturn($pawn, $mainTransaction);
                $comments = $this->cancelComments($request, $pawn);

                $this->cancelPawn($pawn, $request);
                $this->cancelOriginalTransaction($mainTransaction, $comments);

                $office->forceFill([
                    'cash' => round((float) $office->cash + $amountToReturn, 2),
                ])->save();

                $this->createReverseTransaction($pawn, $office, $amountToReturn, $comments);
            });

            return redirect()
                ->route('pawns.show', $pawn->id)
                ->with('success', 'Empeño cancelado correctamente. El efectivo fue devuelto a caja.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('pawns.show', $pawn->id)
                ->with('error', 'No se pudo cancelar el empeño. Revisa la información e inténtalo nuevamente.');
        }
    }

    private function mainPawnTransaction(Pawn $pawn): ?Transaction
    {
        return $pawn->transactions()
            ->where('type', 'pawn')
            ->whereNull('canceled_at')
            ->orderBy('id')
            ->first()
            ?: $pawn->transactions()
                ->whereNull('canceled_at')
                ->orderBy('id')
                ->first();
    }

    private function amountToReturn(Pawn $pawn, Transaction $mainTransaction): float
    {
        $transactionAmount = abs((float) $mainTransaction->amount);

        if ($transactionAmount > 0) {
            return round($transactionAmount, 2);
        }

        return round((float) $pawn->total, 2);
    }

    private function cancelComments(CancelPawnRequest $request, Pawn $pawn): string
    {
        $type = (string) $request->validated('cancellation_type');
        $numberInvestigation = trim((string) ($request->validated('number_investigation') ?? ''));
        $comments = trim((string) ($request->validated('comments_cancel') ?? ''));

        return collect([
            'Cancelación de empeño folio '.$pawn->folio,
            'Motivo: '.$this->cancellationTypeLabel($type),
            $numberInvestigation !== '' ? 'Investigación: '.$numberInvestigation : null,
            $comments !== '' ? 'Comentarios: '.$comments : null,
        ])
            ->filter()
            ->join(' | ');
    }

    private function cancellationTypeLabel(string $type): string
    {
        $types = config('core.cancellation_types', []);

        if (is_array($types) && isset($types[$type])) {
            return (string) $types[$type];
        }

        return match ($type) {
            'capture_error' => 'Error de captura',
            'client_request' => 'Solicitud del cliente',
            'investigation' => 'Investigación',
            'other' => 'Otro',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    private function cancelPawn(Pawn $pawn, CancelPawnRequest $request): void
    {
        $this->safeForceFill($pawn, [
            'canceled_at' => now(),
            'canceled_by' => auth()->id(),
            'cancellation_type' => $request->validated('cancellation_type'),
            'number_investigation' => $request->validated('number_investigation'),
        ]);

        $pawn->save();
    }

    private function cancelOriginalTransaction(Transaction $transaction, string $comments): void
    {
        $this->safeForceFill($transaction, [
            'canceled_at' => now(),
            'canceled_by' => auth()->id(),
            'comments_cancel' => $comments,
        ]);

        $transaction->save();
    }

    private function createReverseTransaction(Pawn $pawn, Office $office, float $amount, string $comments): void
    {
        $transaction = new Transaction();

        $this->safeForceFill($transaction, [
            'company_id' => $pawn->company_id,
            'office_id' => $office->id,
            'pawn_id' => $pawn->id,
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'type' => 'pawn_cancel',
            'amount' => $amount,
            'balance' => (float) $office->cash,
            'payment_type' => 'cash',
            'comments' => $comments,
        ]);

        $transaction->save();
    }

    private function safeForceFill(Model $model, array $values): void
    {
        $table = $model->getTable();

        $filtered = collect($values)
            ->filter(fn ($value, string $column) => Schema::hasColumn($table, $column))
            ->all();

        $model->forceFill($filtered);
    }
}