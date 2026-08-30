<?php

declare(strict_types=1);

use App\Enums\PlaceInvitationStatus;
use App\Enums\PlaceMediaStatus;
use App\Enums\PlaceMediaVariant;
use App\Enums\PlaceMediaVariantStatus;
use App\Enums\PlacePublicLocationPrecision;
use App\Models\PlaceInvitation;
use App\Models\PlaceMedia;
use App\Models\PlaceMediaVariant as PlaceMediaVariantModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('place presentation privacy schema is additive portable and constrained', function (): void {
    expect(Schema::hasColumns('places', ['public_location_precision']))->toBeTrue()
        ->and(Schema::hasTable('place_media'))->toBeTrue()
        ->and(Schema::hasTable('place_media_variants'))->toBeTrue()
        ->and(Schema::hasTable('place_invitations'))->toBeTrue()
        ->and(Schema::hasTable('place_invitation_events'))->toBeTrue()
        ->and(Schema::hasColumns('place_media', [
            'media_key',
            'place_id',
            'content_media_asset_id',
            'attached_by_user_id',
            'status',
            'position',
            'is_featured',
            'attribution',
            'licence',
            'upload_key',
            'moderated_by_user_id',
            'moderated_at',
            'removed_at',
            'recoverable_until',
            'retained_until',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('place_invitations', [
            'invitation_key',
            'place_id',
            'sender_user_id',
            'recipient_user_id',
            'status',
            'message',
            'proposed_at',
            'expires_at',
            'responded_at',
            'revoked_at',
            'idempotency_key',
            'open_key',
        ]))->toBeTrue();

    $indexList = collect(DB::select("PRAGMA index_list('place_media')"))->pluck('name');
    $invitationIndexes = collect(DB::select("PRAGMA index_list('place_invitations')"))->pluck('name');

    expect($indexList)->toContain('place_media_upload_key_unique')
        ->and($indexList)->toContain('place_media_place_position_idx')
        ->and($invitationIndexes)->toContain('place_invitations_idempotency_unique')
        ->and($invitationIndexes)->toContain('place_invitations_open_key_unique');
});

test('new place privacy records use typed lifecycle casts and hide storage and replay material', function (): void {
    $media = PlaceMedia::factory()->create();
    $variant = PlaceMediaVariantModel::factory()->for($media, 'media')->create();
    $invitation = PlaceInvitation::factory()->create();

    expect($media->status)->toBeInstanceOf(PlaceMediaStatus::class)
        ->and($media->toArray())->not->toHaveKeys(['upload_key'])
        ->and($variant->variant)->toBeInstanceOf(PlaceMediaVariant::class)
        ->and($variant->status)->toBeInstanceOf(PlaceMediaVariantStatus::class)
        ->and($variant->toArray())->not->toHaveKeys(['disk', 'path', 'checksum_sha256'])
        ->and($invitation->status)->toBeInstanceOf(PlaceInvitationStatus::class)
        ->and($invitation->toArray())->not->toHaveKeys(['message', 'idempotency_key', 'open_key'])
        ->and(PlacePublicLocationPrecision::ApproximatePoint->value)->toBe('approximate_point');
});
