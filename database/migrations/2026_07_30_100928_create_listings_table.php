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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('owner_key', 80)->index();
            $table->string('owner_name', 120);
            $table->string('owner_initials', 8);
            $table->string('slug', 180)->unique();
            $table->string('type', 40)->index();
            $table->string('category', 80)->index();
            $table->string('title', 160);
            $table->text('description');
            $table->string('condition', 40)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->boolean('is_free')->default(false)->index();
            $table->text('exchange_preferences')->nullable();
            $table->json('species')->nullable();
            $table->string('pet_size', 40)->nullable();
            $table->string('city', 100)->index();
            $table->string('area', 120)->nullable();
            $table->json('delivery_options')->nullable();
            $table->text('meetup_notes')->nullable();
            $table->string('cover_url')->nullable();
            $table->json('gallery')->nullable();
            $table->string('status', 40)->index();
            $table->string('safety_status', 40)->default('community');
            $table->boolean('is_business')->default(false);
            $table->string('business_name', 160)->nullable();
            $table->string('contact_policy', 40)->default('platform-only');
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'type', 'city', 'published_at', 'id'], 'listings_directory_idx');
            $table->index(['category', 'status', 'published_at', 'id'], 'listings_category_status_idx');
            $table->index(['owner_key', 'status'], 'listings_owner_status_idx');
            $table->index(['is_free', 'status', 'published_at'], 'listings_free_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
