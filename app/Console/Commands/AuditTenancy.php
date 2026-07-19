<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Report tenant records with no school.
 *
 * The school scope fails closed, so an unattached row is invisible rather than
 * leaked — but invisible is still broken. This lists what needs attention,
 * with accounts first, because an unattached user is a person who cannot see
 * their own class.
 */
class AuditTenancy extends Command
{
    protected $signature = 'tenancy:audit
                            {--attach= : Assign every unattached record to this school id}';

    protected $description = 'List records that belong to no school';

    /** Tenant tables, most user-visible first. */
    private const TABLES = [
        'users', 'teachers', 'sections', 'students',
        'student_enrollments', 'subjects', 'attendance', 'class_sessions',
    ];

    public function handle(): int
    {
        if ($this->option('attach') !== null) {
            return $this->attach((int) $this->option('attach'));
        }

        $rows = [];
        $total = 0;

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            $query = DB::table($table)->whereNull('school_id');

            // Admins are unscoped by design and are not expected to have one.
            if ($table === 'users') {
                $query->where('role', '!=', 'admin');
            }

            if (Schema::hasColumn($table, 'deleted_at')) {
                $query->whereNull('deleted_at');
            }

            $count = $query->count();
            $total += $count;

            $rows[] = [$table, $count, $count === 0 ? 'ok' : 'NEEDS A SCHOOL'];
        }

        $this->table(['Table', 'Unattached', 'Status'], $rows);

        if ($total === 0) {
            $this->info('Every tenant record belongs to a school.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn("{$total} record(s) belong to no school.");
        $this->line('These are hidden from every non-admin user, including their own owner.');
        $this->line('Assign them a school in the admin area, or re-run the backfill migration.');

        $orphanUsers = DB::table('users')
            ->whereNull('school_id')->where('role', '!=', 'admin')->whereNull('deleted_at')
            ->limit(10)->get(['id', 'email']);

        if ($orphanUsers->isNotEmpty()) {
            $this->newLine();
            $this->line('Accounts that cannot see their own data:');
            foreach ($orphanUsers as $user) {
                $this->line("  #{$user->id}  {$user->email}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Assign every unattached record to one school.
     *
     * Deliberately manual: which tenant an orphaned record belongs to is a
     * judgement about real people's data, so it is confirmed rather than
     * inferred. Admin users are left alone — they are unscoped by design.
     */
    private function attach(int $schoolId): int
    {
        $school = DB::table('schools')->where('id', $schoolId)->whereNull('deleted_at')->first();

        if (! $school) {
            $this->error("No school with id {$schoolId}.");

            return self::FAILURE;
        }

        if (! $this->confirm("Attach every unattached record to \"{$school->name}\"?", false)) {
            $this->line('Nothing changed.');

            return self::SUCCESS;
        }

        $moved = 0;

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            $query = DB::table($table)->whereNull('school_id');

            if ($table === 'users') {
                $query->where('role', '!=', 'admin');
            }

            $count = (clone $query)->count();

            if ($count > 0) {
                $query->update(['school_id' => $schoolId]);
                $this->line("  {$table}: {$count}");
                $moved += $count;
            }
        }

        $this->info("Attached {$moved} record(s) to {$school->name}.");

        return self::SUCCESS;
    }
}
