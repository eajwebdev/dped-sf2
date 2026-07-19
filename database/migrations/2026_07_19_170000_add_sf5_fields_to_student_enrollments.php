<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SF5 (Report on Promotion & Level of Proficiency) reports each learner's
 * general average, the action taken (PROMOTED / *IRREGULAR / RETAINED) and any
 * incomplete subjects. The action is largely derived from promotion_status;
 * what the register lacked was the average itself and the irregular case —
 * a promoted learner (Grade 7 up) still carrying incomplete subjects.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            // 3 decimals because honor learners print theirs to 3 places.
            $table->decimal('general_average', 6, 3)->nullable()->after('promotion_status');
            $table->boolean('is_irregular')->default(false)->after('general_average');

            // The two sub-columns under INCOMPLETE SUBJECT/S.
            $table->string('subjects_completed')->nullable()->after('is_irregular');
            $table->string('subjects_incomplete')->nullable()->after('subjects_completed');
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropColumn(['general_average', 'is_irregular', 'subjects_completed', 'subjects_incomplete']);
        });
    }
};
