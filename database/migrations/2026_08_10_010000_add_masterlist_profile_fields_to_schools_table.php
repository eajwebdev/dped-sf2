<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('short_name')->nullable()->after('name');
            $table->string('previous_name')->nullable()->after('short_name');
            $table->string('mother_school_school_id', 20)->nullable()->after('previous_name');
            $table->string('source_school_year', 20)->nullable()->after('mother_school_school_id');
            $table->string('province')->nullable()->after('region');
            $table->string('municipality')->nullable()->after('province');
            $table->string('district')->nullable()->after('municipality');
            $table->string('legislative_district')->nullable()->after('district');
            $table->string('school_head')->nullable()->after('division');
            $table->string('school_head_designation')->nullable()->after('school_head');
            $table->string('telephone_number')->nullable()->after('school_head_designation');
            $table->string('fax_number')->nullable()->after('telephone_number');
            $table->string('email')->nullable()->after('fax_number');
            $table->date('date_of_operation')->nullable()->after('email');
            $table->string('sub_classification')->nullable()->after('date_of_operation');
            $table->string('curricular_class')->nullable()->after('sub_classification');
            $table->string('school_type')->nullable()->after('curricular_class');
            $table->string('class_organization')->nullable()->after('school_type');

            $table->index(['province', 'municipality'], 'schools_province_municipality_index');
            $table->index(['division', 'district'], 'schools_division_district_index');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropIndex('schools_province_municipality_index');
            $table->dropIndex('schools_division_district_index');
            $table->dropColumn([
                'short_name',
                'previous_name',
                'mother_school_school_id',
                'source_school_year',
                'province',
                'municipality',
                'district',
                'legislative_district',
                'school_head',
                'school_head_designation',
                'telephone_number',
                'fax_number',
                'email',
                'date_of_operation',
                'sub_classification',
                'curricular_class',
                'school_type',
                'class_organization',
            ]);
        });
    }
};
