<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-period attendance, one row per learner per class session.
     *
     * The daily `attendance` table is deliberately left alone: it is what SF2
     * reads and it enforces UNIQUE(student_enrollment_id, attendance_date), so
     * it can never hold more than one row per learner per day. Without a record
     * per period there is no way to tell that a learner sat in period 1 and
     * skipped period 3 — the day's single row just gets overwritten.
     *
     * A row here means "scanned into this session". No row = did not attend it.
     */
    public function up(): void
    {
        Schema::create('class_session_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_enrollment_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['present', 'late'])->default('present');
            $table->time('time_in')->nullable();
            $table->timestamps();

            $table->unique(['class_session_id', 'student_id'], 'class_session_student_unique');
            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_session_attendance');
    }
};
