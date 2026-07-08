<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code', 50)->nullable();

            $table->string('rfc', 20)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();

            // Comisiones
            $table->decimal('storage_commission', 8, 2)->default(0);
            $table->decimal('marketing_commission', 8, 2)->default(0);
            $table->decimal('delayed_payment_commission', 8, 2)->default(0);
            $table->decimal('replacement_contract_commission', 8, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('code');
            $table->index('rfc');
            $table->index('is_active');
            $table->index('deleted_at');

            // Permite varios NULL, pero evita códigos repetidos cuando sí se capturen.
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};