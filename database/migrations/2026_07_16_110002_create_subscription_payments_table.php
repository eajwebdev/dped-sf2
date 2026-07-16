<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ledger of subscription payment attempts. A row is created as `pending`
     * when a teacher starts checkout and flipped to `paid` by the PayMongo
     * webhook, which also extends the user's subscribed_until date.
     */
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('paymongo');
            $table->string('provider_reference')->nullable();   // checkout session / payment id
            $table->unsignedInteger('amount');                   // centavos (₱299 = 29900)
            $table->string('currency', 3)->default('PHP');
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('provider_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
