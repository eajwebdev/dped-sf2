<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Owner-granted 100% free access: the account bypasses trial/subscription
     * checks entirely while the flag is on. Billing dates are left untouched,
     * so revoking simply drops the account back to its real subscription state.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('free_access')->default(false)->after('subscribed_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('free_access');
        });
    }
};
