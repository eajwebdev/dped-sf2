<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-managed non-class exceptions. school_year_id null = applies to all years
     * (e.g. regular national holidays); is_recurring = same month/day every year.
     */
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('name');
            $table->enum('type', [
                'holiday', 'special_holiday', 'suspension', 'typhoon', 'no_class',
            ])->default('holiday');
            $table->boolean('is_recurring')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['school_year_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
