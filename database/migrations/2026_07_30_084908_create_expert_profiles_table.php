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
        Schema::create('expert_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('owner_key', 80)->index();
            $table->string('slug', 120)->unique();
            $table->string('public_name', 120);
            $table->string('legal_name', 160)->nullable();
            $table->string('primary_type', 80)->index();
            $table->string('headline', 180);
            $table->text('bio');
            $table->text('approach')->nullable();
            $table->text('boundaries')->nullable();
            $table->unsignedSmallInteger('years_experience')->default(0);
            $table->string('country', 80)->index();
            $table->string('city', 100)->index();
            $table->string('service_area', 160)->nullable();
            $table->json('specializations');
            $table->json('species');
            $table->json('age_groups')->nullable();
            $table->json('languages');
            $table->json('formats');
            $table->json('methods')->nullable();
            $table->json('workplaces')->nullable();
            $table->json('accessibility')->nullable();
            $table->json('professional_interests')->nullable();
            $table->string('availability_status', 60)->default('available');
            $table->string('response_time', 80)->nullable();
            $table->boolean('accepts_new_clients')->default(true);
            $table->boolean('offers_emergency_care')->default(false);
            $table->decimal('price_from', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->string('status', 40)->default('draft')->index();
            $table->string('verification_status', 40)->default('unsubmitted')->index();
            $table->boolean('identity_verified')->default(false);
            $table->boolean('education_verified')->default(false);
            $table->boolean('qualification_verified')->default(false);
            $table->boolean('license_verified')->default(false);
            $table->boolean('workplace_verified')->default(false);
            $table->boolean('organization_verified')->default(false);
            $table->boolean('contact_verified')->default(false);
            $table->timestamp('verification_expires_at')->nullable()->index();
            $table->timestamp('next_available_at')->nullable()->index();
            $table->string('avatar_url')->nullable();
            $table->string('cover_url')->nullable();
            $table->decimal('review_average', 3, 2)->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->unsignedInteger('verified_review_count')->default(0);
            $table->unsignedInteger('forum_answer_count')->default(0);
            $table->unsignedInteger('publication_count')->default(0);
            $table->timestamps();

            $table->index(
                ['status', 'verification_status', 'primary_type', 'city'],
                'expert_profiles_directory_idx',
            );
            $table->index(
                ['accepts_new_clients', 'next_available_at', 'id'],
                'expert_profiles_availability_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expert_profiles');
    }
};
