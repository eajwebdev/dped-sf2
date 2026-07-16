<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A live "class in session": the teacher starts it, the section's learners
     * are seeded absent for the day, and a short qr_key is handed to whoever
     * runs the QR scanner. Scanning a learner flips them to present.
     */
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('session_date');
            $table->string('qr_key', 12);                    // secret handed to the scanner
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            // Only one qr_key may be live at a time.
            $table->unique(['qr_key', 'status'], 'class_sessions_key_status_unique');
            $table->index(['section_id', 'session_date']);
            $table->index(['teacher_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
