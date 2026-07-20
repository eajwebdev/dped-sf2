<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SF9 "Report on Learner's Observed Values": one mark per learner, per core
     * value, per period. Mark is a non-numerical rating (AO/SO/RO/NO). `period`
     * follows the same 1..4 scheme as learner_grades.
     */
    public function up(): void
    {
        Schema::create('learner_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_enrollment_id')->constrained()->cascadeOnDelete();
            $table->string('core_value', 20); // maka_diyos, makatao, maka_kalikasan, maka_bansa
            $table->unsignedTinyInteger('period'); // 1..4
            $table->string('mark', 2)->nullable(); // AO, SO, RO, NO
            $table->timestamps();

            $table->unique(['student_enrollment_id', 'core_value', 'period'], 'learner_value_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learner_values');
    }
};
