<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_media_assets', function (Blueprint $table): void {
            $table->id();
            $table->ulid('media_key')->unique();
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('media_type', 24);
            $table->string('status', 32);
            $table->string('disk', 40);
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('byte_size');
            $table->string('checksum_sha256', 64);
            $table->text('alt_text')->nullable();
            $table->text('licence')->nullable();
            $table->json('safe_metadata')->nullable();
            $table->timestamp('retained_until')->nullable();
            $table->timestamps();

            $table->unique(['disk', 'path'], 'content_media_assets_disk_path_unique');
            $table->index(
                ['owner_user_id', 'status', 'created_at', 'id'],
                'content_media_assets_owner_status_idx',
            );
            $table->index(
                ['created_by_user_id', 'created_at', 'id'],
                'content_media_assets_creator_idx',
            );
            $table->index(
                ['status', 'media_type', 'created_at', 'id'],
                'content_media_assets_processing_idx',
            );
            $table->index(
                ['checksum_sha256', 'byte_size', 'id'],
                'content_media_assets_checksum_idx',
            );
            $table->index(
                ['retained_until', 'status', 'id'],
                'content_media_assets_retention_idx',
            );
        });

        Schema::create('content_publication_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_publication_id')
                ->constrained('content_publications')
                ->cascadeOnDelete();
            $table->foreignId('content_media_asset_id')
                ->constrained('content_media_assets')
                ->restrictOnDelete();
            $table->unsignedSmallInteger('position');
            $table->boolean('is_cover')->default(false);
            $table->text('caption')->nullable();
            $table->timestamps();

            $table->unique(
                ['content_publication_id', 'content_media_asset_id'],
                'content_publication_media_asset_unique',
            );
            $table->unique(
                ['content_publication_id', 'position'],
                'content_publication_media_position_unique',
            );
            $table->index(
                ['content_media_asset_id', 'content_publication_id'],
                'content_publication_media_reverse_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_publication_media');
        Schema::dropIfExists('content_media_assets');
    }
};
