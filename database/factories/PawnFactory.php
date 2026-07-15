<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Department;
use App\Models\Office;
use App\Models\Pawn;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class PawnFactory extends Factory
{
    protected $model = Pawn::class;

    public function definition(): array
    {
        $office = Office::query()->inRandomOrder()->first();

        if (! $office) {
            throw new RuntimeException('Se necesita al menos una sucursal antes de crear empeños de prueba.');
        }

        $customerQuery = Customer::query();

        if (Schema::hasColumn('customers', 'company_id')) {
            $customerQuery->where('company_id', $office->company_id);
        }

        $customer = $customerQuery->inRandomOrder()->first();

        if (! $customer) {
            throw new RuntimeException('Se necesita al menos un cliente antes de crear empeños de prueba.');
        }

        $userQuery = User::query();

        if (Schema::hasColumn('users', 'company_id')) {
            $userQuery->where('company_id', $office->company_id);
        }

        $user = $userQuery->inRandomOrder()->first();
        $department = Department::query()
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (! $user || ! $department) {
            throw new RuntimeException('Se necesita al menos un usuario y un departamento activo.');
        }

        $createdAt = now()->subDays(fake()->numberBetween(1, 25))->startOfDay()->addHours(10);
        $term = (int) ($department->term ?: 30);
        $auctionDays = (int) ($department->auction ?: 30);
        $total = fake()->randomFloat(2, 500, 12000);

        return [
            'folio' => ((int) Pawn::query()->where('office_id', $office->id)->max('folio')) + 1,
            'customer_id' => $customer->id,
            'company_id' => $office->company_id,
            'office_id' => $office->id,
            'created_by' => $user->id,
            'canceled_by' => null,
            'auction_by' => null,
            'previous_pawn' => null,
            'canceled_at' => null,
            'paid_at' => null,
            'auction_at' => null,
            'date_expiration' => $createdAt->copy()->addDays($term)->toDateString(),
            'date_auction' => $createdAt->copy()->addDays($term + $auctionDays)->toDateString(),
            'date_settlement' => $createdAt->copy()->addDays($term)->toDateString(),
            'total' => $total,
            'estimated_value' => round($total * 1.75, 2),
            'loan_value' => $total,
            'loan_rate' => (float) $department->loan_rate,
            'iva_rate' => (float) $department->iva_rate,
            'monthly_interest_rate' => (float) $department->monthly_interest_rate,
            'daily_interest_rate' => (float) $department->daily_interest_rate,
            'term' => $term,
            'auction' => $auctionDays,
            'pay_extra' => 0,
            'comments' => fake()->sentence(),
            'photos' => null,
            'beneficiary' => $customer->name,
            'bag' => 'TEST-'.fake()->unique()->numerify('#####'),
            'inapam_rate' => 0,
            'cancellation_type' => null,
            'number_investigation' => null,
            'paid_by' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function readyForAuction(int $daysOverdue = 10): static
    {
        return $this->state(function (array $attributes) use ($daysOverdue) {
            $term = (int) ($attributes['term'] ?? 30);
            $auctionDays = (int) ($attributes['auction'] ?? 30);
            $auctionDate = today()->subDays($daysOverdue);
            $createdAt = $auctionDate->copy()->subDays($term + $auctionDays)->addHours(10);

            return [
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'date_expiration' => $auctionDate->copy()->subDays($auctionDays)->toDateString(),
                'date_auction' => $auctionDate->toDateString(),
                'paid_at' => null,
                'canceled_at' => null,
                'auction_at' => null,
            ];
        });
    }

    public function paid(?int $userId = null, int $daysAgo = 3): static
    {
        return $this->state(fn () => [
            'paid_at' => now()->subDays($daysAgo),
            'paid_by' => $userId,
            'canceled_at' => null,
            'auction_at' => null,
        ]);
    }

    public function canceled(?int $userId = null, int $daysAgo = 3): static
    {
        return $this->state(fn () => [
            'canceled_at' => now()->subDays($daysAgo),
            'canceled_by' => $userId,
            'cancellation_type' => 'capture_error',
            'paid_at' => null,
            'auction_at' => null,
        ]);
    }

    public function auctioned(?int $userId = null, int $daysAgo = 3): static
    {
        return $this->readyForAuction($daysAgo + 1)->state(fn () => [
            'auction_at' => now()->subDays($daysAgo),
            'auction_by' => $userId,
            'paid_at' => null,
            'canceled_at' => null,
        ]);
    }

    public function countersignOf(Pawn $pawn): static
    {
        return $this->state(fn () => [
            'customer_id' => $pawn->customer_id,
            'company_id' => $pawn->company_id,
            'office_id' => $pawn->office_id,
            'previous_pawn' => $pawn->id,
            'total' => (float) $pawn->total,
            'loan_value' => (float) $pawn->loan_value,
            'estimated_value' => (float) $pawn->estimated_value,
        ]);
    }
}