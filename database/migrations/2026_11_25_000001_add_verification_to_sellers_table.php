<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A seller must send a picture of their national ID and stays "pending"
 * until an admin approves the application.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            // Stored on the private disk (storage/app/private/national-ids), never publicly linked.
            $table->string('national_id_image', 255)->nullable()->after('official_name');
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending')->after('national_id_image');
            $table->timestamp('reviewed_at')->nullable()->after('verification_status');
            $table->index('verification_status', 'idx_sellers_verification');
        });

        // Sellers that already existed before this rule keep selling.
        DB::table('sellers')->update(['verification_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropIndex('idx_sellers_verification');
            $table->dropColumn(['national_id_image', 'verification_status', 'reviewed_at']);
        });
    }
};
