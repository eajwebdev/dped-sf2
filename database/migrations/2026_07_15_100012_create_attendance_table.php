<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One canonical attendance record per learner per class day (the DepEd SF2 model).
     * Keyed to the enrollment so history is preserved across school years; section_id,
     * school_year_id and student_id are denormalized for fast SF2 aggregation and to
     * avoid N+1 / join blow-up at 500k+ rows. subject_assignment_id records the offering
     * context through which the entry was made (nullable = homeroom/adviser entry).
     */
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('attendance_date');
            $table->enum('status', [
                'present', 'absent', 'late', 'excused', 'half_day', 'no_class',
            ])->default('present');
            $table->time('time_in')->nullable();
            $table->boolean('is_locked')->default(false);   // set once past the edit-lock window
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_enrollment_id', 'attendance_date'], 'attendance_enrollment_date_unique');
            $table->index(['school_year_id', 'section_id', 'attendance_date'], 'attendance_year_section_date_idx');
            $table->index(['student_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
