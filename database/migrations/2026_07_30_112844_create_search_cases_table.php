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
        Schema::create('search_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('owner_key', 80)->index();
            $table->string('owner_name', 120);
            $table->string('owner_initials', 8);
            $table->string('coordinator_key', 80)->nullable()->index();
            $table->string('coordinator_name', 120)->nullable();
            $table->string('slug', 180)->unique();
            $table->string('public_code', 20)->unique();
            $table->string('active_key', 180)->nullable()->unique();
            $table->string('type', 30)->index();
            $table->string('status', 40)->index();
            $table->string('moderation_status', 40)->default('approved')->index();
            $table->string('pet_profile_key', 80)->nullable()->index();
            $table->string('pet_name', 100);
            $table->string('species', 60)->index();
            $table->string('breed', 120)->nullable();
            $table->string('sex', 20)->nullable();
            $table->string('age_label', 80)->nullable();
            $table->string('size', 30)->nullable();
            $table->string('primary_color', 80);
            $table->string('coat', 80)->nullable();
            $table->text('distinctive_marks')->nullable();
            $table->text('hidden_marks')->nullable();
            $table->text('description');
            $table->text('health_notice')->nullable();
            $table->text('approach_instructions')->nullable();
            $table->text('avoid_instructions')->nullable();
            $table->json('accessories')->nullable();
            $table->string('microchip_status', 30)->default('unknown');
            $table->string('last_seen_area', 160)->index();
            $table->string('city', 100)->index();
            $table->string('country', 2)->default('LT');
            $table->decimal('public_latitude', 9, 6)->nullable();
            $table->decimal('public_longitude', 9, 6)->nullable();
            $table->text('exact_location')->nullable();
            $table->string('direction', 100)->nullable();
            $table->timestamp('last_seen_at')->index();
            $table->timestamp('reported_at')->index();
            $table->unsignedSmallInteger('notification_radius_km')->default(5);
            $table->string('visibility', 40)->default('public')->index();
            $table->boolean('alerts_active')->default(true)->index();
            $table->boolean('volunteer_join_open')->default(true);
            $table->boolean('animal_secured')->default(false);
            $table->boolean('contact_protected')->default(true);
            $table->text('contact_details')->nullable();
            $table->string('contact_token', 64)->unique();
            $table->string('cover_url')->nullable();
            $table->json('photos')->nullable();
            $table->json('risk_flags')->nullable();
            $table->text('latest_update')->nullable();
            $table->timestamp('last_sighting_at')->nullable()->index();
            $table->timestamp('found_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('closed_at')->nullable()->index();
            $table->string('closure_reason', 80)->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();

            $table->index(
                ['status', 'city', 'last_seen_at', 'id'],
                'search_cases_status_city_seen_idx',
            );
            $table->index(
                ['type', 'species', 'status', 'last_seen_at'],
                'search_cases_type_species_status_idx',
            );
            $table->index(
                ['owner_key', 'status', 'updated_at'],
                'search_cases_owner_status_idx',
            );
            $table->index(
                ['alerts_active', 'city', 'updated_at'],
                'search_cases_alerts_city_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_cases');
    }
};
