<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Configurable behaviour for the attendance module. school_year_id null = the
     * global default row; a per-year row overrides it when present.
     */
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('edit_lock_days')->default(7);       // days after which records lock
            $table->unsignedSmallInteger('autosave_seconds')->default(15);    // client autosave interval
            $table->boolean('block_future_dates')->default(true);
            $table->boolean('allow_holiday_override')->default(false);        // teachers may mark on non-class days
            $table->boolean('count_late_as_present')->default(true);         // for daily-attendance totals
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
