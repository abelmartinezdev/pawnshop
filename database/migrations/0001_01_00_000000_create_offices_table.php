<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('offices', function (Blueprint $table) {
      $table->id();

      $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

      $table->string('name');                 // Indeco, Loreto
      $table->string('code')->nullable();     // opcional, para UI (equivalente a branch.code)
      $table->string('serie')->nullable();    // prefijo para folios
      $table->string('phone')->nullable();
      $table->string('address')->nullable();

      $table->string('schedule')->nullable();
      $table->string('bank_account')->nullable();

      $table->decimal('daily_interest_rate', 8, 4)->default(0);
      $table->decimal('monthly_interest_rate', 8, 4)->default(0);

      $table->decimal('cash', 12, 2)->default(0);

      $table->timestamps();
      $table->softDeletes();

      $table->index(['company_id', 'name']);
      $table->unique(['company_id', 'name', 'deleted_at']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('offices');
  }
};