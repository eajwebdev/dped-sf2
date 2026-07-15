<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records an administrator override that re-opens a locked date (past the
     * edit-lock window) for a given section so a teacher can amend attendance.
     */
    public function up(): void
    {
        Schema::create('attendance_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('unlocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['section_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_unlocks');
    }
};
