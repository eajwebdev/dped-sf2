<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-learner, per-subject, per-period grade behind the SF9 report card.
     * `period` is 1..4 — the four quarters for JHS, or the two quarters of each
     * of the two semesters for SHS (1,2 = 1st sem; 3,4 = 2nd sem). Final ratings
     * and general averages are computed from these, never stored.
     */
    public function up(): void
    {
        Schema::create('learner_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('period'); // 1..4
            $table->decimal('grade', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['student_enrollment_id', 'subject_id', 'period'], 'learner_grade_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learner_grades');
    }
};
