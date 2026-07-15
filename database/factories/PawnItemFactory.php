<?php

namespace Database\Factories;

use App\Models\Pawn;
use App\Models\PawnItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use RuntimeException;

class PawnItemFactory extends Factory
{
    protected $model = PawnItem::class;

    public function definition(): array
    {
        $pawn = Pawn::query()->inRandomOrder()->first();
        $product = Product::query()
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (! $pawn || ! $product) {
            throw new RuntimeException('Se necesita al menos un empeño y un producto activo.');
        }

        return [
            'pawn_id' => $pawn->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'description' => fake()->randomElement([
                'Artículo en buen estado, funcionamiento verificado.',
                'Artículo usado con señales normales de uso.',
                'Artículo completo con accesorios.',
                'Prenda revisada y recibida para prueba histórica.',
            ]),
            'value' => fake()->randomFloat(2, 300, 8000),
        ];
    }
}