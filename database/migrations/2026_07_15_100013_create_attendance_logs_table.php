<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Immutable change history for attendance records (who changed what, when).
     * Distinct from audit_logs (system-wide) so attendance edits stay queryable
     * per record without scanning the global audit table.
     */
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->nullable()->constrained('attendance')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('action', ['created', 'updated', 'deleted', 'unlocked'])->default('updated');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('old_remarks')->nullable();
            $table->text('new_remarks')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('attendance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
