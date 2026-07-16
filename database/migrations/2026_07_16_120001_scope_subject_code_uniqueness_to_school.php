<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Subject codes are unique per school now that subjects are tenant-scoped,
     * so two schools can each define e.g. "MATH7".
     */
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_code_unique');
            $table->unique(['school_id', 'code'], 'subjects_school_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_school_code_unique');
            $table->unique('code', 'subjects_code_unique');
        });
    }
};
