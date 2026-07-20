<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The education level(s) a school offers. Elementary stands alone; Junior
     * and Senior High may share one campus, so the combined value covers that.
     * Nullable so schools created before this stay valid until next edited.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('education_level', 20)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('education_level');
        });
    }
};
