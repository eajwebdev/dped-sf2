<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The official DepEd SF9 marks a behaviour statement under each core value, not
 * the core value as a whole (e.g. Maka-Diyos has two statements). Add a
 * `behavior` index (1-based) so a learner carries one mark per behaviour
 * statement, per period. Existing rows map to statement 1.
 *
 * The behaviour-aware unique index is added before the old one is dropped: on
 * MySQL the student_enrollment_id foreign key needs a leftmost index to lean
 * on, and both indexes begin with that column.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('learner_values', 'behavior')) {
            Schema::table('learner_values', function (Blueprint $table) {
                $table->unsignedTinyInteger('behavior')->default(1)->after('core_value');
            });
        }

        Schema::table('learner_values', function (Blueprint $table) {
            $table->unique(
                ['student_enrollment_id', 'core_value', 'behavior', 'period'],
                'learner_value_behavior_unique'
            );
        });

        Schema::table('learner_values', function (Blueprint $table) {
            $table->dropUnique('learner_value_unique');
        });
    }

    public function down(): void
    {
        // Collapse to one row per (enrolment, core value, period) so the older,
        // narrower unique key can be restored without collisions.
        DB::table('learner_values')->where('behavior', '>', 1)->delete();

        Schema::table('learner_values', function (Blueprint $table) {
            $table->unique(['student_enrollment_id', 'core_value', 'period'], 'learner_value_unique');
        });

        Schema::table('learner_values', function (Blueprint $table) {
            $table->dropUnique('learner_value_behavior_unique');
        });

        Schema::table('learner_values', function (Blueprint $table) {
            $table->dropColumn('behavior');
        });
    }
};
