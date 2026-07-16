<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant root for the SaaS. Each school is created by a platform admin and
     * carries the DepEd School ID that teachers select at registration.
     */
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('school_id')->unique();   // DepEd School ID (e.g. "123456")
            $table->string('name');
            $table->string('division')->nullable();
            $table->string('region')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
