<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g. "Grade 7"
            $table->string('code')->unique();                // e.g. "G7"
            $table->unsignedSmallInteger('level_order');     // ordering for promotion (1..N)
            $table->boolean('is_graduating')->default(false); // terminal grade (e.g. Grade 6 / 12)
            $table->timestamps();
            $table->softDeletes();

            $table->index('level_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_levels');
    }
};
