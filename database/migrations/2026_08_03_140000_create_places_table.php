<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->restrictOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_edited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('slug', 190)->unique();
            $table->string('creation_idempotency_key', 190)->unique();
            $table->string('name', 180);
            $table->text('summary')->nullable();
            $table->string('type', 50);
            $table->string('visibility', 30)->default('public');
            $table->string('status', 30)->default('active');
            $table->string('locale', 10)->default('en');
            $table->string('public_region', 160);
            $table->string('public_address', 500)->nullable();
            $table->decimal('public_latitude', 9, 6)->nullable();
            $table->decimal('public_longitude', 9, 6)->nullable();
            $table->text('exact_address')->nullable();
            $table->text('exact_latitude')->nullable();
            $table->text('exact_longitude')->nullable();
            $table->text('private_instructions')->nullable();
            $table->boolean('is_indoor')->default(false);
            $table->string('verification_status', 40)->default('not_assessed');
            $table->string('verification_source', 255)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('information_expires_at')->nullable();
            $table->string('accessibility_status', 50)->default('not_assessed');
            $table->json('accessibility_facts')->nullable();
            $table->text('transport_information')->nullable();
            $table->text('parking_information')->nullable();
            $table->text('pet_rules')->nullable();
            $table->json('species_rules')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->text('metadata')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['visibility', 'status', 'public_region', 'id'], 'places_public_directory_idx');
            $table->index(['type', 'status', 'id'], 'places_type_status_idx');
            $table->index(['organization_id', 'status', 'id'], 'places_organization_status_idx');
            $table->index(['owner_user_id', 'status', 'id'], 'places_owner_status_idx');
            $table->index('created_by_user_id', 'places_created_by_idx');
            $table->index('last_edited_by_user_id', 'places_last_edited_by_idx');
            $table->index(['verification_status', 'information_expires_at', 'id'], 'places_verification_expiry_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
