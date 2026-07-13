<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pawns', 'auction_at')) {
            Schema::table('pawns', function (Blueprint $table) {
                $table->timestamp('auction_at')
                    ->nullable()
                    ->after('paid_at');
            });
        }

        if (! Schema::hasColumn('pawns', 'auction_by')) {
            Schema::table('pawns', function (Blueprint $table) {
                $table->foreignId('auction_by')
                    ->nullable()
                    ->after('auction_at')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('products', 'auction_mode')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('auction_mode', 20)
                    ->default('sellable')
                    ->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pawns', 'auction_by')) {
            Schema::table('pawns', function (Blueprint $table) {
                $table->dropConstrainedForeignId('auction_by');
            });
        }

        if (Schema::hasColumn('products', 'auction_mode')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('auction_mode');
            });
        }

        /*
         * No eliminamos auction_at porque ese campo puede pertenecer
         * a una migración anterior del sistema.
         */
    }
};