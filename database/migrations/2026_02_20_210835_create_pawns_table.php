<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pawns', function (Blueprint $table) {
            $table->id();

            // Folio NUMÉRICO (tu accessor arma serie + padding)
            $table->unsignedBigInteger('folio');

            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('office_id')->constrained('offices');

            // Usuario que crea / cancela
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('canceled_by')->nullable()->constrained('users');

            // Refrendo (countersign): pawn previo
            $table->foreignId('previous_pawn')->nullable()->constrained('pawns');

            // Estado / fechas
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('paid_at')->nullable(); // tu modelo usa isPaid()

            $table->date('date_expiration')->nullable();
            $table->date('date_auction')->nullable();
            $table->timestamp('auction_at')->nullable(); // tu canBeAuctioned() lo usa

            $table->date('date_settlement')->nullable();

            // Montos / tasas
            $table->decimal('total', 12, 2)->default(0); // principal
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->decimal('loan_value', 12, 2)->nullable();

            $table->decimal('loan_rate', 8, 4)->nullable();             // %
            $table->decimal('iva_rate', 8, 4)->nullable();              // %
            $table->decimal('monthly_interest_rate', 8, 4)->nullable(); // %
            $table->decimal('daily_interest_rate', 8, 4)->nullable();   // %

            // Otros
            $table->unsignedInteger('term')->nullable();
            $table->unsignedInteger('auction')->nullable(); // días para subasta (lo usas en updateDateExpiration)
            $table->decimal('pay_extra', 12, 2)->nullable();

            $table->text('comments')->nullable();
            $table->text('photos')->nullable();     // legacy: "url;url;..."
            $table->string('beneficiary')->nullable();
            $table->string('bag')->nullable();

            // Descuento INAPAM (tu código lo usa)
            $table->decimal('inapam_rate', 8, 4)->nullable(); // ej 0.10

            $table->timestamps();

            // Índices útiles
            $table->index(['office_id', 'created_at']);
            $table->index(['company_id', 'created_at']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['previous_pawn']);
            $table->unique(['office_id', 'folio']); // folio único por sucursal
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pawns');
    }
};