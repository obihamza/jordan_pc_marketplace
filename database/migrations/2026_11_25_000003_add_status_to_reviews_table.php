<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** A review is shown on the site only after an admin approves it. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('comment');
            $table->timestamp('reviewed_at')->nullable()->after('status');
            $table->index('status', 'idx_reviews_status');
        });

        // Reviews that already exist stay visible.
        DB::table('reviews')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_status');
            $table->dropColumn(['status', 'reviewed_at']);
        });
    }
};
