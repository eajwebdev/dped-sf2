<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->index(['is_active', 'name'], 'schools_active_name_index');
            $table->index(['is_active', 'school_id'], 'schools_active_school_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropIndex('schools_active_name_index');
            $table->dropIndex('schools_active_school_id_index');
        });
    }
};
