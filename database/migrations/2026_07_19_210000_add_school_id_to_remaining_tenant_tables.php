<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Give the remaining tenant tables their own school_id.
 *
 * These were reachable only through a scoped parent — textbooks through a
 * section, for instance — so a single missing ownership check in a controller
 * was all that stood between one school and another's records. Carrying the
 * column means the global scope defends them directly, rather than relying on
 * every future query remembering to join through the parent.
 */
return new class extends Migration
{
    /**
     * table => [parent table, foreign key on this table]
     *
     * Only textbooks and their issuances are scoped by the model layer. The
     * calendar, holidays and attendance settings derive from school_years,
     * which is shared across every tenant, so they stay shared too — a public
     * holiday is not one school's secret. They take the column anyway, so a
     * future per-school override has somewhere to live.
     */
    private const TABLES = [
        'textbooks' => ['sections', 'section_id'],
        'textbook_issuances' => ['student_enrollments', 'student_enrollment_id'],
        'holidays' => [null, null],
        'school_calendar' => [null, null],
        'attendance_settings' => [null, null],
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => [$parent, $foreignKey]) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) {
                // Nullable: existing rows are backfilled below, and a row that
                // genuinely has no owner must not block the migration.
                $t->foreignId('school_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $t->index('school_id');
            });

            if ($parent !== null) {
                DB::statement("
                    update {$table}
                    set school_id = (
                        select p.school_id from {$parent} p
                        where p.id = {$table}.{$foreignKey} and p.school_id is not null
                        limit 1
                    )
                    where school_id is null
                ");
            }
        }

        // Rows with no parent to inherit from belong to the only school, when
        // there is exactly one. With several, leave them for tenancy:audit.
        $schools = DB::table('schools')->whereNull('deleted_at')->limit(2)->pluck('id');

        if ($schools->count() === 1) {
            foreach (array_keys(self::TABLES) as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_id')) {
                    DB::table($table)->whereNull('school_id')->update(['school_id' => (int) $schools->first()]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::TABLES) as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropConstrainedForeignId('school_id');
                });
            }
        }
    }
};
