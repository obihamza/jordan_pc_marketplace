<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_id')
                ->constrained('sellers', 'user_id')
                ->cascadeOnUpdate();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate();
            $table->string('name', 180);
            $table->string('brand', 100)->nullable();
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock_quantity')->default(1);
            $table->enum('condition_type', ['new', 'used']);
            $table->enum('listing_status', ['available', 'sold', 'hidden', 'reserved'])
                ->nullable()
                ->default('available');
            $table->string('product_variant', 250)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable();

            $table->index('seller_id', 'idx_products_seller');
            $table->index('category_id', 'idx_products_category');
            $table->index('listing_status', 'idx_products_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
