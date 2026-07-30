<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->restrictOnDelete();
            $table->foreignId('reservation_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference', 40)->unique();
            $table->uuid('idempotency_key')->unique();
            $table->string('buyer_key', 80)->index();
            $table->string('buyer_name', 120);
            $table->string('seller_key', 80)->index();
            $table->string('seller_name', 120);
            $table->string('order_kind', 40)->index();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('delivery_amount', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->string('delivery_method', 40);
            $table->string('public_delivery_area', 180)->nullable();
            $table->string('status', 40)->index();
            $table->string('payment_status', 40)->index();
            $table->json('item_snapshot');
            $table->json('terms_snapshot');
            $table->timestamp('ordered_at')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['buyer_key', 'status', 'ordered_at', 'id'], 'orders_buyer_status_idx');
            $table->index(['seller_key', 'status', 'ordered_at', 'id'], 'orders_seller_status_idx');
            $table->index(['listing_id', 'status', 'ordered_at'], 'orders_listing_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
