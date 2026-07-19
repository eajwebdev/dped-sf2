<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An upgrade behaves differently from a purchase: it tops up the price
 * difference for the months already paid for and moves the tier without moving
 * the end date. The ledger has to say which happened, both so the webhook
 * applies the right effect and so the audit trail reads correctly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('kind', 20)->default('purchase')->after('plan');
            $table->string('previous_plan', 30)->nullable()->after('kind');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn(['kind', 'previous_plan']);
        });
    }
};
