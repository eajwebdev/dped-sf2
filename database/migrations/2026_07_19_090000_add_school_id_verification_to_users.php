<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Teachers self-register, so an administrator has to be able to confirm the
 * applicant really belongs to the school they picked. Registration now requires
 * a photo of their school-issued ID, which the approving admin reviews.
 *
 * The file lives on the private disk and is served only through an authorised
 * route — it is an identity document, not a public asset.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('school_id_document_path')->nullable()->after('school_id');
            $table->string('school_id_number', 60)->nullable()->after('school_id_document_path');
            $table->timestamp('school_id_verified_at')->nullable()->after('school_id_number');
            $table->foreignId('school_id_verified_by')->nullable()->after('school_id_verified_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id_verified_by');
            $table->dropColumn(['school_id_document_path', 'school_id_number', 'school_id_verified_at']);
        });
    }
};
