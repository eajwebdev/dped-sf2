<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Abandoning a checkout at the gateway is a distinct outcome from a payment
 * that was attempted and declined, and the audit trail needs to tell them
 * apart. The status column was an enum of pending/paid/failed, so 'cancelled'
 * has to be admitted before it can be written.
 *
 * Enum changes are dialect-specific: MySQL needs a MODIFY, while SQLite
 * enforces enums with a CHECK constraint that can only be changed by rebuilding
 * the table, which Doctrine-free Laravel does by recreating it.
 */
return new class extends Migration
{
    private const STATUSES = ['pending', 'paid', 'failed', 'cancelled'];

    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $list = "'".implode("','", self::STATUSES)."'";
            DB::statement("ALTER TABLE subscription_payments MODIFY COLUMN status ENUM($list) NOT NULL DEFAULT 'pending'");

            return;
        }

        // SQLite (and anything else): rebuild the column without the old CHECK.
        // The composite index covers `status`, so it has to come down first —
        // SQLite refuses to drop a column an index still references.
        Schema::table('subscription_payments', function ($table) {
            $table->dropIndex(['user_id', 'status']);
            $table->string('status_new', 20)->default('pending');
        });

        DB::table('subscription_payments')->update(['status_new' => DB::raw('status')]);

        Schema::table('subscription_payments', function ($table) {
            $table->dropColumn('status');
        });

        Schema::table('subscription_payments', function ($table) {
            $table->renameColumn('status_new', 'status');
        });

        Schema::table('subscription_payments', function ($table) {
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        // Anything cancelled becomes failed so it still fits the narrower set.
        DB::table('subscription_payments')->where('status', 'cancelled')->update(['status' => 'failed']);

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE subscription_payments MODIFY COLUMN status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending'");
        }
    }
};
