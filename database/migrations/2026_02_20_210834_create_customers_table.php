<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Legacy
            $table->string('name', 190);
            $table->string('state', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('address', 255)->nullable();

            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('rfc', 20)->nullable();

            // En legacy eran IDs (catálogos). Los dejamos así para no bloquear.
            $table->unsignedBigInteger('code_id')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();

            // INAPAM
            $table->string('inapam_code', 50)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices útiles
            $table->index(['name']);
            $table->index(['rfc']);
            $table->index(['email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};