<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('departments')) {
            return;
        }

        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            $table->string('code', 100)->unique();
            $table->string('description', 255);

            $table->unsignedInteger('auction')->nullable();
            $table->unsignedInteger('term')->nullable();

            $table->decimal('loan_rate', 8, 3)->nullable();
            $table->decimal('daily_interest_rate', 8, 3)->nullable();
            $table->decimal('monthly_interest_rate', 8, 3)->nullable();
            $table->decimal('iva_rate', 8, 3)->nullable();

            $table->decimal('cat_annual', 8, 3)->nullable();
            $table->decimal('cat_annual_noiva', 8, 3)->nullable();

            $table->string('color', 50)->nullable();
            $table->string('icon', 100)->nullable();

            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();

            $table->index('description');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};