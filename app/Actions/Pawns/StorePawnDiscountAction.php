<?php

namespace App\Actions\Pawns;

use App\Http\Requests\Pawns\StorePawnDiscountRequest;
use App\Models\Office;
use App\Models\Pawn;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class StorePawnDiscountAction
{
    public function __invoke(StorePawnDiscountRequest $request, Pawn $pawn): RedirectResponse
    {
        if (! auth()->user()?->can('apply-discount')) {
            abort(403, 'No tienes permiso para aplicar descuentos.');
        }

        try {
            DB::transaction(function () use ($request, $pawn) {
                $pawn = Pawn::query()
                    ->with(['office', 'items'])
                    ->lockForUpdate()
                    ->findOrFail($pawn->id);

                if (! $this->canApplyDiscount($pawn)) {
                    throw ValidationException::withMessages([
                        'days' => 'No se puede aplicar descuento a este empeño.',
                    ]);
                }

                $office = Office::query()
                    ->lockForUpdate()
                    ->findOrFail($pawn->office_id);

                $days = (int) $request->validated('days');

                $currentDays = (int) $pawn->days2payminus1;
                $alreadyDiscountedDays = method_exists($pawn, 'interestDiscountDays')
                    ? $pawn->interestDiscountDays()
                    : 0;

                $maxDays = max($currentDays - $alreadyDiscountedDays, 0);

                if ($maxDays <= 0) {
                    throw ValidationException::withMessages([
                        'days' => 'Este empeño ya no tiene días disponibles para descontar.',
                    ]);
                }

                if ($days > $maxDays) {
                    throw ValidationException::withMessages([
                        'days' => "Solo puedes descontar hasta {$maxDays} días.",
                    ]);
                }

                $this->createDiscountTransaction(
                    pawn: $pawn,
                    office: $office,
                    days: $days,
                    comments: trim((string) ($request->validated('comments') ?? ''))
                );
            });

            return redirect()
                ->route('pawns.show', $pawn->id)
                ->with('success', 'Descuento aplicado correctamente.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('pawns.show', $pawn->id)
                ->with('error', 'No se pudo aplicar el descuento. Revisa la información e inténtalo nuevamente.');
        }
    }

    private function canApplyDiscount(Pawn $pawn): bool
    {
        return $pawn->auction_at === null
            && $pawn->paid_at === null
            && $pawn->canceled_at === null
            && ! $pawn->hasCountersign();
    }

    private function createDiscountTransaction(Pawn $pawn, Office $office, int $days, string $comments = ''): Transaction
    {
        $dailyInterestWithIva = round((float) $pawn->getDailyInterest(true), 2);
        $dailyInterestWithoutIva = round((float) $pawn->getDailyInterest(false), 2);

        $discountAmount = round($dailyInterestWithIva * $days, 2);
        $discountAmountWithoutIva = round($dailyInterestWithoutIva * $days, 2);

        $daysBefore = (int) $pawn->days2payminus1;
        $daysAlreadyDiscounted = method_exists($pawn, 'interestDiscountDays')
            ? $pawn->interestDiscountDays()
            : 0;

        $daysAfter = max($daysBefore - $daysAlreadyDiscounted - $days, 0);

        $interestBeforeDiscount = round($dailyInterestWithIva * $daysBefore, 2);
        $interestDiscountBefore = method_exists($pawn, 'interestDiscountAmount')
            ? round($pawn->interestDiscountAmount(true), 2)
            : 0;

        $interestAfterDiscount = round(max(
            $interestBeforeDiscount - $interestDiscountBefore - $discountAmount - (float) $pawn->paidAmount(),
            0
        ), 2);

        $data = [
            'discount_days' => $days,
            'days_before' => $daysBefore,
            'days_already_discounted' => $daysAlreadyDiscounted,
            'days_after' => $daysAfter,

            'daily_interest' => $dailyInterestWithIva,
            'daily_interest_without_iva' => $dailyInterestWithoutIva,

            'discount_amount' => $discountAmount,
            'discount_amount_without_iva' => $discountAmountWithoutIva,

            'interest_before_discount' => $interestBeforeDiscount,
            'interest_discount_before' => $interestDiscountBefore,
            'interest_after_discount' => $interestAfterDiscount,

            'paid_amount' => round((float) $pawn->paidAmount(), 2),
            'amount_after_discount' => round((float) $pawn->total + $interestAfterDiscount, 2),

            'comments' => $comments,
        ];

        $transaction = new Transaction();

        $this->safeForceFill($transaction, [
            'company_id' => $pawn->company_id,
            'office_id' => $office->id,
            'pawn_id' => $pawn->id,
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'type' => 'interest_days_discount',
            'amount' => 0,
            'balance' => (float) $office->cash,
            'payment_type' => 'cash',
            'comments' => $comments !== ''
                ? $comments
                : 'Descuento de '.$days.' día(s) de interés. Folio '.$pawn->folio,
            'data' => json_encode($data),
        ]);

        $transaction->save();

        return $transaction;
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