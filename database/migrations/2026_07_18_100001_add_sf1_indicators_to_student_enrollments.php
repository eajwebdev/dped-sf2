<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The SF1 REMARKS column carries an indicator code plus the "required
 * information" the legend demands for that code. T/O, T/I, DRP and LE are
 * derived from the enrolment's own status; the remaining four indicators
 * (CCT, B/A, LWD, ACL) and the detail text each code requires live here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            // T/O and T/I — name of Public (P) / Private (PR) school + effectivity date.
            $table->string('transfer_school')->nullable()->after('remarks');
            $table->date('transfer_date')->nullable()->after('transfer_school');
            // DRP — reason and effectivity date.
            $table->string('dropped_reason')->nullable()->after('transfer_date');
            $table->date('dropped_date')->nullable()->after('dropped_reason');
            // LE reuses the existing is_late_enrollment flag; this is its reason.
            $table->string('late_enrollment_reason')->nullable()->after('dropped_date');

            // CCT — control/reference number & effectivity date.
            $table->string('cct_reference')->nullable()->after('late_enrollment_reason');
            // B/A — name of school last attended & year.
            $table->string('balik_aral_detail')->nullable()->after('cct_reference');
            // LWD — specify the disability.
            $table->string('disability_detail')->nullable()->after('balik_aral_detail');
            // ACL — specify level & effectivity date.
            $table->string('accelerated_detail')->nullable()->after('disability_detail');
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'transfer_school', 'transfer_date', 'dropped_reason', 'dropped_date',
                'late_enrollment_reason', 'cct_reference', 'balik_aral_detail',
                'disability_detail', 'accelerated_detail',
            ]);
        });
    }
};
