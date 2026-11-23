<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('image_path');
            $table->boolean('is_primary')->nullable()->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index('product_id', 'idx_product_images_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
