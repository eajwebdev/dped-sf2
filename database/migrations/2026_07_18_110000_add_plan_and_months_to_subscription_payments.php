<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscriptions moved from a single fixed monthly charge to three plans that
 * can be bought several months in advance, so the ledger has to record which
 * plan was purchased and how many months the payment covered.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('plan', 30)->default('starter')->after('provider_reference');
            $table->unsignedTinyInteger('months')->default(1)->after('plan');
            $table->unsignedTinyInteger('discount_percent')->default(0)->after('amount');
        });

        Schema::table('users', function (Blueprint $table) {
            // The plan the teacher last paid for — drives entitlements later.
            $table->string('subscription_plan', 30)->nullable()->after('subscribed_until');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn(['plan', 'months', 'discount_percent']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('subscription_plan');
        });
    }
};
