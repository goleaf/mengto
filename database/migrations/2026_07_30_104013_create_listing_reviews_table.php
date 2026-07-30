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
        Schema::create('listing_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->string('reviewer_key', 80)->index();
            $table->string('reviewer_name', 120);
            $table->boolean('is_verified_buyer')->default(true)->index();
            $table->unsignedTinyInteger('item_rating');
            $table->unsignedTinyInteger('seller_rating');
            $table->unsignedTinyInteger('delivery_rating')->nullable();
            $table->text('body');
            $table->string('status', 40)->default('published')->index();
            $table->text('seller_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->index(
                ['listing_id', 'status', 'is_verified_buyer', 'created_at'],
                'listing_reviews_listing_status_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_reviews');
    }
};
