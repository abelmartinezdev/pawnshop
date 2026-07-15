<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pawns')) {
            return;
        }

        if (! Schema::hasColumn('pawns', 'cancellation_type')) {
            Schema::table('pawns', function (Blueprint $table) {
                $table->string('cancellation_type', 50)
                    ->nullable()
                    ->after('canceled_at');
            });
        }

        if (! Schema::hasColumn('pawns', 'number_investigation')) {
            Schema::table('pawns', function (Blueprint $table) {
                $table->string('number_investigation', 100)
                    ->nullable()
                    ->after('cancellation_type');
            });
        }

        if (! Schema::hasColumn('pawns', 'paid_by')) {
            $userIdMethod = $this->userIdColumnMethod();

            Schema::table('pawns', function (Blueprint $table) use ($userIdMethod) {
                $table->{$userIdMethod}('paid_by')
                    ->nullable()
                    ->after('paid_at');

                $table->index('paid_by', 'pawns_paid_by_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pawns', 'paid_by')) {
            Schema::table('pawns', function (Blueprint $table) {
                $table->dropIndex('pawns_paid_by_index');
                $table->dropColumn('paid_by');
            });
        }

        if (Schema::hasColumn('pawns', 'number_investigation')) {
            Schema::table('pawns', function (Blueprint $table) {
                $table->dropColumn('number_investigation');
            });
        }

        if (Schema::hasColumn('pawns', 'cancellation_type')) {
            Schema::table('pawns', function (Blueprint $table) {
                $table->dropColumn('cancellation_type');
            });
        }
    }

    private function userIdColumnMethod(): string
    {
        $type = Schema::hasColumn('pawns', 'created_by')
            ? Schema::getColumnType('pawns', 'created_by')
            : Schema::getColumnType('users', 'id');

        return in_array($type, ['bigint', 'bigInteger'], true)
            ? 'unsignedBigInteger'
            : 'unsignedInteger';
    }
};
