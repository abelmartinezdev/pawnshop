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
      $table->string('rfc')->nullable();
      $table->string('phone')->nullable();
      $table->string('email')->nullable();
      $table->string('website')->nullable();
      $table->string('address')->nullable();

      // comisiones (por si las usarás)
      $table->decimal('storage_commission', 8, 2)->default(0);
      $table->decimal('marketing_commission', 8, 2)->default(0);
      $table->decimal('delayed_payment_commission', 8, 2)->default(0);
      $table->decimal('replacement_contract_commission', 8, 2)->default(0);

      $table->timestamps();
      $table->softDeletes();

      $table->unique(['name', 'deleted_at']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('companies');
  }
};