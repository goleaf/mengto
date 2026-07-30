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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('reviewer_key', 80)->index();
            $table->string('reviewer_name', 120);
            $table->boolean('is_verified_client')->default(false)->index();
            $table->boolean('is_anonymous')->default(false);
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('communication_rating')->nullable();
            $table->unsignedTinyInteger('clarity_rating')->nullable();
            $table->unsignedTinyInteger('organization_rating')->nullable();
            $table->unsignedTinyInteger('price_transparency_rating')->nullable();
            $table->text('body');
            $table->string('status', 40)->default('published')->index();
            $table->text('expert_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->index(
                ['expert_profile_id', 'status', 'is_verified_client', 'created_at'],
                'reviews_profile_status_verified_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
