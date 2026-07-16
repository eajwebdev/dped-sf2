<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant-scoping columns. Data becomes school-shared: every roster row
     * belongs to a school so teachers only see their own school's records.
     * School years, grade levels and the calendar stay global (nationwide).
     */
    private array $tables = [
        'teachers',
        'students',
        'sections',
        'subjects',
        'subject_assignments',
        'teacher_subject_assignments',
        'student_enrollments',
        'teacher_schedules',
        'attendance',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('school_id')->nullable()->after('id')
                    ->constrained('schools')->nullOnDelete();
                $t->index('school_id');
            });
        }

        // A section name is unique per grade + year *within a school*.
        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique('sections_year_grade_name_unique');
            $table->unique(['school_id', 'school_year_id', 'grade_level_id', 'name'], 'sections_school_year_grade_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique('sections_school_year_grade_name_unique');
            $table->unique(['school_year_id', 'grade_level_id', 'name'], 'sections_year_grade_name_unique');
        });

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('school_id');
            });
        }
    }
};
