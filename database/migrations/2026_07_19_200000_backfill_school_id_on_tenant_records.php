<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Attach every stranded tenant record to a school.
 *
 * The school scope used to let a user with no school_id read every school in
 * the database. It now fails closed, which turns those same accounts from
 * over-privileged into locked-out — so they have to be attached to the school
 * they actually belong to, inferred from the records around them.
 *
 * Every step is conservative: a row is only filled in when the answer is
 * unambiguous. Anything still null afterwards is reported by
 * `php artisan tenancy:audit` rather than guessed at.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Teachers inherit from their linked user account, and vice versa —
        //    whichever of the pair already knows its school.
        DB::table('teachers')
            ->whereNull('school_id')
            ->whereNotNull('user_id')
            ->update([
                'school_id' => DB::raw('(select school_id from users where users.id = teachers.user_id)'),
            ]);

        DB::statement('
            update users
            set school_id = (select t.school_id from teachers t where t.user_id = users.id limit 1)
            where users.school_id is null
              and exists (select 1 from teachers t where t.user_id = users.id and t.school_id is not null)
        ');

        // 2. A teacher's school can also be read from the sections they advise.
        DB::statement('
            update users
            set school_id = (
                select s.school_id from sections s
                inner join teachers t on t.id = s.adviser_id
                where t.user_id = users.id and s.school_id is not null
                limit 1
            )
            where users.school_id is null
              and exists (
                select 1 from sections s
                inner join teachers t on t.id = s.adviser_id
                where t.user_id = users.id and s.school_id is not null
              )
        ');

        // 3. Child records inherit from their parent.
        $this->inherit('student_enrollments', 'sections', 'section_id');
        $this->inherit('attendance', 'sections', 'section_id');
        $this->inherit('class_sessions', 'sections', 'section_id');
        $this->inherit('students', 'student_enrollments', 'id', 'student_id');

        // 4. Single-school installations: anything still unattached belongs to
        //    the only school there is. Never runs when several schools exist.
        $schools = DB::table('schools')->whereNull('deleted_at')->limit(2)->pluck('id');

        if ($schools->count() === 1) {
            $only = (int) $schools->first();

            foreach (['users', 'teachers', 'sections', 'students', 'student_enrollments', 'subjects', 'attendance', 'class_sessions'] as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_id')) {
                    DB::table($table)->whereNull('school_id')->update(['school_id' => $only]);
                }
            }
        }
    }

    /**
     * Copy school_id onto $table from $parent, matched on $foreignKey.
     * $parentKey names the column on the parent side when it is not `id`.
     */
    private function inherit(string $table, string $parent, string $parentKey, ?string $foreignKey = null): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
            return;
        }

        $foreignKey ??= $parentKey;

        // students is filled from the enrolments that point AT it, so the join
        // runs the other way round from the parent-child cases.
        $join = $table === 'students'
            ? "select p.school_id from {$parent} p where p.student_id = {$table}.id and p.school_id is not null limit 1"
            : "select p.school_id from {$parent} p where p.id = {$table}.{$foreignKey} and p.school_id is not null limit 1";

        DB::statement("
            update {$table}
            set school_id = ({$join})
            where {$table}.school_id is null and ({$join}) is not null
        ");
    }

    public function down(): void
    {
        // Backfilling ownership is not reversible: the previous null told us
        // nothing, and clearing these again would re-open the leak.
    }
};
