<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A subject_assignment represents a subject OFFERED to a specific section
     * in a specific school year (the "class offering"). Teachers are attached
     * to it via teacher_subject_assignments.
     */
    public function up(): void
    {
        Schema::create('subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['section_id', 'subject_id'], 'subject_assignment_section_subject_unique');
            $table->index(['school_year_id', 'section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_assignments');
    }
};
