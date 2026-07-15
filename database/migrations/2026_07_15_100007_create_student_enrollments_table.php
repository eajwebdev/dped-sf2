<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->enum('status', [
                'enrolled', 'transferred_in', 'transferred_out',
                'dropped', 'promoted', 'retained', 'graduated',
            ])->default('enrolled');
            $table->enum('promotion_status', ['pending', 'promoted', 'retained', 'graduated'])->default('pending');
            $table->date('enrollment_date');
            $table->boolean('is_late_enrollment')->default(false); // enrolled beyond the June cut-off (for SF2)
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // One active enrollment per student per school year (history preserved across years).
            $table->unique(['student_id', 'school_year_id'], 'enrollment_student_year_unique');
            $table->index(['school_year_id', 'section_id']);
            $table->index(['section_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
