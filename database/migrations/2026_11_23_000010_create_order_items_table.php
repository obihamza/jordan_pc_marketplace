<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate();
            // seller_id references sellers.user_id
            $table->foreignId('seller_id')
                ->constrained('sellers', 'user_id')
                ->cascadeOnUpdate();
            // Snapshots taken at purchase time
            $table->string('product_name', 180);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->enum('item_status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])
                ->default('pending');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
