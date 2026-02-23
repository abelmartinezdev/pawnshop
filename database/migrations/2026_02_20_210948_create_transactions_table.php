<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('office_id')->constrained('offices');
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->foreignId('pawn_id')->nullable()->constrained('pawns')->nullOnDelete();

            // Para relacionar con otros registros (ventas, subastas, etc.)
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('type'); // pawn, countersign, liquidation, payment_to_interest, expense_by_*, etc.

            $table->text('comments')->nullable();
            $table->text('data')->nullable(); // json legacy

            $table->decimal('amount', 12, 2)->default(0);   // + entra, - sale
            $table->decimal('balance', 12, 2)->default(0);  // saldo sucursal al momento

            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->decimal('discount_rate', 8, 4)->nullable();

            $table->enum('payment_type', ['cash', 'card'])->default('cash');

            $table->timestamp('canceled_at')->nullable();
            $table->text('comments_cancel')->nullable();

            $table->timestamps();

            $table->index(['office_id', 'created_at']);
            $table->index(['pawn_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['payment_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};