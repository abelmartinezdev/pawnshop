<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('closures', function (Blueprint $table) {
            if (! Schema::hasColumn('closures', 'period_start_at')) {
                $table->timestamp('period_start_at')->nullable();
            }

            if (! Schema::hasColumn('closures', 'period_end_at')) {
                $table->timestamp('period_end_at')->nullable();
            }

            if (! Schema::hasColumn('closures', 'closed_at')) {
                $table->timestamp('closed_at')->nullable();
            }

            if (! Schema::hasColumn('closures', 'opening_cash')) {
                $table->decimal('opening_cash', 14, 2)->default(0);
            }

            if (! Schema::hasColumn('closures', 'cash_in')) {
                $table->decimal('cash_in', 14, 2)->default(0);
            }

            if (! Schema::hasColumn('closures', 'cash_out')) {
                $table->decimal('cash_out', 14, 2)->default(0);
            }

            if (! Schema::hasColumn('closures', 'expected_cash')) {
                $table->decimal('expected_cash', 14, 2)->default(0);
            }

            if (! Schema::hasColumn('closures', 'counted_cash')) {
                $table->decimal('counted_cash', 14, 2)->default(0);
            }

            if (! Schema::hasColumn('closures', 'difference')) {
                $table->decimal('difference', 14, 2)->default(0);
            }

            if (! Schema::hasColumn('closures', 'total_transactions')) {
                $table->unsignedInteger('total_transactions')->default(0);
            }

            if (! Schema::hasColumn('closures', 'comments')) {
                $table->text('comments')->nullable();
            }

            if (! Schema::hasColumn('closures', 'data')) {
                $table->json('data')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('closures', function (Blueprint $table) {
            $columns = [
                'period_start_at',
                'period_end_at',
                'closed_at',
                'opening_cash',
                'cash_in',
                'cash_out',
                'expected_cash',
                'counted_cash',
                'difference',
                'total_transactions',
                'comments',
                'data',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('closures', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};