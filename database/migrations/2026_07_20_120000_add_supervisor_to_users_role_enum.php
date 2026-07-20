<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Widen the users.role column to admit 'supervisor' (school head / principal),
 * the read-only oversight role. Existing 'admin'/'teacher' values are untouched.
 *
 * MySQL keeps the enum, extended in place. Other drivers (SQLite in tests)
 * rendered the original enum as a CHECK constraint that only allows
 * admin/teacher, so there we convert role to a plain string, dropping that
 * constraint. Laravel 12 changes columns natively — no doctrine/dbal needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin', 'teacher', 'supervisor') NOT NULL DEFAULT 'teacher'");

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('teacher')->change();
        });
    }

    public function down(): void
    {
        // Any supervisors would violate the narrowed enum/constraint, so retire
        // them first, regardless of driver.
        DB::table('users')->where('role', 'supervisor')->update(['role' => 'teacher']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin', 'teacher') NOT NULL DEFAULT 'teacher'");

            return;
        }

        // Non-MySQL: leave role as a plain string. The two-value CHECK is only
        // restored by rolling back the original add-role migration.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('teacher')->change();
        });
    }
};
