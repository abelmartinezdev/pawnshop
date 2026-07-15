<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\Pawn;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $office = Office::query()->inRandomOrder()->first();

        if (! $office) {
            throw new RuntimeException('Se necesita al menos una sucursal para crear transacciones.');
        }

        $userQuery = User::query();

        if (Schema::hasColumn('users', 'company_id')) {
            $userQuery->where('company_id', $office->company_id);
        }

        $user = $userQuery->inRandomOrder()->first();
        $pawn = Pawn::query()
            ->where('office_id', $office->id)
            ->inRandomOrder()
            ->first();

        if (! $user) {
            throw new RuntimeException('Se necesita al menos un usuario para crear transacciones.');
        }

        return [
            'office_id' => $office->id,
            'user_id' => $user->id,
            'pawn_id' => $pawn?->id,
            'reference_id' => null,
            'type' => Transaction::TYPE_ADJUSTMENT,
            'comments' => 'Movimiento generado por factory.',
            'data' => ['source' => 'factory'],
            'amount' => 0,
            'balance' => (float) $office->cash,
            'discount_amount' => 0,
            'discount_rate' => 0,
            'payment_type' => Transaction::PAYMENT_CASH,
            'canceled_at' => null,
            'comments_cancel' => null,
        ];
    }
}