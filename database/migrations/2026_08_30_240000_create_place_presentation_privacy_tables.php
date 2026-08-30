<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table): void {
            $table->string('public_location_precision', 32)
                ->nullable()
                ->after('public_longitude');
        });

        Schema::table('place_access_grants', function (Blueprint $table): void {
            $table->string('revocation_idempotency_key', 64)->nullable()->unique();
        });

        Schema::create('place_media', function (Blueprint $table): void {
            $table->id();
            $table->ulid('media_key')->unique();
            $table->foreignId('place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('content_media_asset_id')
                ->constrained('content_media_assets')
                ->restrictOnDelete();
            $table->foreignId('attached_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('moderated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('pending_review');
            $table->unsignedSmallInteger('position')->default(1);
            $table->boolean('is_featured')->default(false);
            $table->string('featured_key', 190)->nullable()->unique();
            $table->text('caption')->nullable();
            $table->text('attribution');
            $table->string('licence', 80);
            $table->string('upload_key', 64);
            $table->string('moderation_reason_code', 120)->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->timestamp('recoverable_until')->nullable();
            $table->timestamp('retained_until')->nullable();
            $table->timestamp('legal_hold_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique('upload_key', 'place_media_upload_key_unique');
            $table->unique(
                ['place_id', 'content_media_asset_id'],
                'place_media_place_asset_unique',
            );
            $table->index(
                ['place_id', 'status', 'position', 'id'],
                'place_media_place_position_idx',
            );
            $table->index(
                ['status', 'recoverable_until', 'id'],
                'place_media_retention_idx',
            );
        });

        Schema::create('place_media_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_media_id')->constrained('place_media')->cascadeOnDelete();
            $table->string('variant', 24);
            $table->string('status', 24)->default('ready');
            $table->string('disk', 40);
            $table->string('path', 500);
            $table->string('mime_type', 120);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedBigInteger('byte_size');
            $table->string('checksum_sha256', 64);
            $table->string('failure_code', 80)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['place_media_id', 'variant'],
                'place_media_variants_media_variant_unique',
            );
            $table->unique(['disk', 'path'], 'place_media_variants_disk_path_unique');
        });

        Schema::create('place_media_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_media_id')->constrained('place_media')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 60);
            $table->string('reason_code', 120)->nullable();
            $table->string('idempotency_key', 64)->unique();
            $table->text('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index(
                ['place_media_id', 'created_at', 'id'],
                'place_media_events_media_idx',
            );
        });

        Schema::create('place_invitations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('invitation_key')->unique();
            $table->foreignId('place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('recipient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('responded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 24)->default('pending');
            $table->string('visibility', 24)->default('private');
            $table->text('message')->nullable();
            $table->timestamp('proposed_at')->nullable();
            $table->timestamp('sent_at');
            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('reason_code', 120)->nullable();
            $table->string('idempotency_key', 64);
            $table->string('response_key', 64)->nullable();
            $table->string('revocation_key', 64)->nullable();
            $table->string('open_key', 64)->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique('idempotency_key', 'place_invitations_idempotency_unique');
            $table->unique('response_key', 'place_invitations_response_key_unique');
            $table->unique('revocation_key', 'place_invitations_revocation_key_unique');
            $table->unique('open_key', 'place_invitations_open_key_unique');
            $table->index(
                ['recipient_user_id', 'status', 'expires_at', 'id'],
                'place_invitations_recipient_status_idx',
            );
            $table->index(
                ['sender_user_id', 'status', 'id'],
                'place_invitations_sender_status_idx',
            );
        });

        Schema::create('place_invitation_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_invitation_id')->constrained('place_invitations')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 60);
            $table->string('reason_code', 120)->nullable();
            $table->string('idempotency_key', 64)->unique();
            $table->text('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index(
                ['place_invitation_id', 'created_at', 'id'],
                'place_invitation_events_invitation_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_invitation_events');
        Schema::dropIfExists('place_invitations');
        Schema::dropIfExists('place_media_events');
        Schema::dropIfExists('place_media_variants');
        Schema::dropIfExists('place_media');

        Schema::table('place_access_grants', function (Blueprint $table): void {
            $table->dropColumn('revocation_idempotency_key');
        });

        Schema::table('places', function (Blueprint $table): void {
            $table->dropColumn('public_location_precision');
        });
    }
};
