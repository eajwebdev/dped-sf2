<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SF1 (School Register) asks for a fuller learner profile than SF2 needed:
 * birth place, mother tongue, ethnicity, religion, a four-part address, and
 * both parents by name. The legacy single-line `address` is kept so existing
 * records stay readable and can be migrated at the school's own pace.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('birth_place')->nullable()->after('birthdate');       // Province of birth
            $table->string('mother_tongue')->nullable()->after('birth_place');
            $table->string('ethnic_group')->nullable()->after('mother_tongue');  // IP — blank when not IP
            $table->string('religion')->nullable()->after('ethnic_group');

            // SF1 splits the address across four columns.
            $table->string('address_street')->nullable()->after('address');      // House # / Street / Sitio / Purok
            $table->string('address_barangay')->nullable()->after('address_street');
            $table->string('address_municipality')->nullable()->after('address_barangay');
            $table->string('address_province')->nullable()->after('address_municipality');

            $table->string('father_name')->nullable()->after('address_province');
            $table->string('mother_name')->nullable()->after('father_name');     // Maiden: first, middle, last
            $table->string('guardian_relationship', 50)->nullable()->after('guardian_name');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'birth_place', 'mother_tongue', 'ethnic_group', 'religion',
                'address_street', 'address_barangay', 'address_municipality', 'address_province',
                'father_name', 'mother_name', 'guardian_relationship',
            ]);
        });
    }
};
