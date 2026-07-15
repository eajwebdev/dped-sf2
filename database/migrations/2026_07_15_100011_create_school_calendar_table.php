<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Authoritative per-day calendar for a school year. Generated from the year's
     * start/end range minus weekends minus holidays; the SF2 "No. of Days of Classes"
     * and attendance-day validation read from here. is_override lets admins force a
     * class day (or non-class day) regardless of the default rules.
     */
    public function up(): void
    {
        Schema::create('school_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('day_type', [
                'school_day', 'weekend', 'holiday', 'suspension', 'no_class',
            ])->default('school_day');
            $table->boolean('is_class_day')->default(true); // denormalized flag for fast counts
            $table->boolean('is_override')->default(false);
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['school_year_id', 'date'], 'calendar_year_date_unique');
            $table->index(['school_year_id', 'is_class_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_calendar');
    }
};
