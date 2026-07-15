<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\PawnItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use RuntimeException;

class AuctionFactory extends Factory
{
    protected $model = Auction::class;

    public function definition(): array
    {
        $item = PawnItem::query()
            ->with('pawn')
            ->inRandomOrder()
            ->first();

        if (! $item || ! $item->pawn) {
            throw new RuntimeException('Se necesita al menos un artículo de empeño para crear remates.');
        }

        $value = round((float) $item->value, 2);
        $interest = round($value * fake()->randomFloat(3, 0.08, 0.35), 2);

        return [
            'company_id' => $item->pawn->company_id,
            'office_id' => $item->pawn->office_id,
            'pawn_id' => $item->pawn_id,
            'pawn_item_id' => $item->id,
            'product_id' => $item->product_id,
            'user_id' => null,
            'created_by' => $item->pawn->created_by,
            'unit_number' => 1,
            'quantity' => (float) $item->quantity,
            'auction_mode' => Auction::MODE_SELLABLE,
            'description' => $item->description,
            'source_value' => $value,
            'value' => $value,
            'interest_amount' => $interest,
            'total' => round($value + $interest, 2),
            'commission' => 0,
            'active' => true,
            'not_sell' => false,
            'sold_at' => null,
            'move_at' => null,
        ];
    }

    public function grouped(): static
    {
        return $this->state(fn () => [
            'auction_mode' => Auction::MODE_GROUPED,
            'active' => true,
            'not_sell' => false,
        ]);
    }

    public function notSell(): static
    {
        return $this->state(fn () => [
            'auction_mode' => Auction::MODE_NOT_SELL,
            'active' => false,
            'not_sell' => true,
        ]);
    }

    public function sold(int $userId, mixed $date = null): static
    {
        return $this->state(fn () => [
            'user_id' => $userId,
            'active' => false,
            'sold_at' => $date ?: now(),
        ]);
    }

    public function moved(mixed $date = null): static
    {
        return $this->state(fn () => [
            'active' => false,
            'move_at' => $date ?: now(),
        ]);
    }
}