<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SF3 (Books Issued and Returned) tracks, per section, which textbook titles
 * exist and when each learner received and returned their copy.
 *
 * A textbook belongs to a section rather than a subject row because SF3 lists
 * whatever titles the adviser actually handed out — including workbooks and
 * modules that never appear in the timetable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('textbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('subject_area', 80);
            $table->string('title');
            $table->unsignedTinyInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['section_id', 'sort']);
        });

        Schema::create('textbook_issuances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('textbook_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_enrollment_id')->constrained()->cascadeOnDelete();
            $table->date('issued_at')->nullable();
            $table->date('returned_at')->nullable();

            // Unreturned-book code printed in the Date Returned cell:
            // FM (force majeure), TDO (transferred/dropout), NEG (negligence).
            $table->string('return_code', 5)->nullable();

            // Action-taken code for the REMARKS column: LLTR, TLTR, PTL.
            $table->string('action_code', 5)->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            // One copy of one title per learner — SF3 has exactly one cell for it.
            $table->unique(['textbook_id', 'student_enrollment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('textbook_issuances');
        Schema::dropIfExists('textbooks');
    }
};
