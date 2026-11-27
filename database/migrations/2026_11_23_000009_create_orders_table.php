<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate();
            $table->foreignId('address_id')
                ->constrained('addresses')
                ->cascadeOnUpdate();
            $table->foreignId('coupon_id')
                ->nullable()
                ->constrained('coupons')
                ->nullOnDelete();
            $table->string('order_number', 50)->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->nullable()->default(0);
            $table->decimal('shipping_cost', 10, 2)->nullable()->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('order_status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])
                ->nullable()
                ->default('pending');

            $table->string('shipping_address');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('order_status', 'idx_orders_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
