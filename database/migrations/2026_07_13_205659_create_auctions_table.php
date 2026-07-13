<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignId('office_id')
                ->constrained('offices')
                ->restrictOnDelete();

            $table->foreignId('pawn_id')
                ->constrained('pawns')
                ->restrictOnDelete();

            $table->foreignId('pawn_item_id')
                ->constrained('pawn_items')
                ->restrictOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unsignedInteger('unit_number')->default(1);
            $table->decimal('quantity', 12, 3)->default(1);

            /*
             * sellable = vendible por unidad
             * grouped  = conservar toda la cantidad junta
             * not_sell = no se puede vender desde remates
             */
            $table->string('auction_mode', 20)->default('sellable');

            $table->text('description');

            /*
             * source_value:
             * Valor original registrado en pawn_items.
             *
             * value:
             * Parte proporcional del préstamo asignada al registro.
             */
            $table->decimal('source_value', 14, 3)->default(0);
            $table->decimal('value', 14, 3)->default(0);
            $table->decimal('interest_amount', 14, 3)->default(0);
            $table->decimal('total', 14, 3)->default(0);
            $table->decimal('commission', 14, 3)->default(0);

            $table->boolean('active')->default(true);
            $table->boolean('not_sell')->default(false);

            $table->timestamp('sold_at')->nullable();
            $table->timestamp('move_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['pawn_item_id', 'unit_number'],
                'auctions_item_unit_unique'
            );

            $table->index(['office_id', 'active', 'not_sell']);
            $table->index(['pawn_id', 'sold_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};