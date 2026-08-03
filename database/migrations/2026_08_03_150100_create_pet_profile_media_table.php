<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_profile_media', function (Blueprint $table): void {
            $table->id();
            $table->ulid('media_key')->unique();
            $table->foreignId('pet_profile_id')
                ->constrained('pet_profiles')
                ->restrictOnDelete();
            $table->foreignId('content_media_asset_id')
                ->constrained('content_media_assets')
                ->restrictOnDelete();
            $table->foreignId('attached_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('role', 32)->default('primary');
            $table->string('status', 32);
            $table->string('current_key', 190)->nullable()->unique();
            $table->string('upload_key', 64)->unique();
            $table->timestamp('recoverable_until')->nullable();
            $table->timestamp('replaced_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['pet_profile_id', 'content_media_asset_id'],
                'pet_profile_media_profile_asset_unique',
            );
            $table->index(
                ['pet_profile_id', 'role', 'status', 'created_at', 'id'],
                'pet_profile_media_profile_role_status_idx',
            );
            $table->index(
                ['recoverable_until', 'status', 'id'],
                'pet_profile_media_recovery_idx',
            );
            $table->index(
                ['content_media_asset_id', 'pet_profile_id'],
                'pet_profile_media_asset_profile_idx',
            );
            $table->index(
                ['attached_by_user_id', 'id'],
                'pet_profile_media_attached_by_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_profile_media');
    }
};
