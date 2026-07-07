<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pawn_items')) {
            return;
        }

        Schema::create('pawn_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pawn_id')
                ->constrained('pawns')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->decimal('quantity', 12, 3)->default(1);
            $table->text('description');
            $table->decimal('value', 12, 3)->default(0);

            $table->timestamps();

            $table->index(['pawn_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pawn_items');
    }
};