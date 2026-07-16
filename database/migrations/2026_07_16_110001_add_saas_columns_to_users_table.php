<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SaaS account state for teacher subscriptions: which school the teacher
     * joined, approval state, the free-trial window and the paid-through date.
     * Default status is `approved` so existing seeded admin/teacher accounts keep
     * working; self-registration explicitly sets new teachers to `pending`.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('role')
                ->constrained('schools')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])
                ->default('approved')->after('is_active');
            $table->string('contact_number')->nullable()->after('status');
            $table->timestamp('trial_ends_at')->nullable()->after('contact_number');
            $table->date('subscribed_until')->nullable()->after('trial_ends_at');
            $table->timestamp('approved_at')->nullable()->after('subscribed_until');
            $table->foreignId('approved_by')->nullable()->after('approved_at')
                ->constrained('users')->nullOnDelete();

            $table->index('status');
        });

        // Back-fill: any account that predates this migration is already trusted.
        DB::table('users')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'status', 'contact_number', 'trial_ends_at',
                'subscribed_until', 'approved_at',
            ]);
        });
    }
};
