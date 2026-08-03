<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_id')->unique()->constrained('places')->restrictOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->restrictOnDelete();
            $table->string('status', 40)->default('active');
            $table->string('timezone', 64);
            $table->unsignedInteger('human_capacity')->nullable();
            $table->unsignedInteger('animal_capacity')->nullable();
            $table->json('species_capacities')->nullable();
            $table->unsignedInteger('staff_to_participant_ratio')->nullable();
            $table->text('operational_contact')->nullable();
            $table->text('operational_rules')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('information_expires_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status', 'id'], 'venues_organization_status_idx');
            $table->index(['status', 'information_expires_at', 'id'], 'venues_status_expiry_idx');
        });

        Schema::create('venue_areas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->string('stable_key', 190);
            $table->string('name', 180);
            $table->string('type', 50);
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('human_capacity')->nullable();
            $table->unsignedInteger('animal_capacity')->nullable();
            $table->json('species_capacities')->nullable();
            $table->string('accessibility_status', 50)->default('not_assessed');
            $table->json('accessibility_facts')->nullable();
            $table->text('private_instructions')->nullable();
            $table->timestamps();

            $table->unique(['venue_id', 'stable_key'], 'venue_areas_venue_key_unique');
            $table->index(['venue_id', 'type', 'id'], 'venue_areas_venue_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_areas');
        Schema::dropIfExists('venues');
    }
};
