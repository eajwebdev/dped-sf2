<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each school can run its own active school year. NULL means "follow the
     * global active year" (school_years.is_active), which keeps every existing
     * school and all legacy accounts working unchanged.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->foreignId('active_school_year_id')->nullable()->after('is_active')
                ->constrained('school_years')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropConstrainedForeignId('active_school_year_id');
        });
    }
};
