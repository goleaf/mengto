<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ContentMediaStatus;
use App\Enums\ContentMediaType;
use App\Enums\PetProfileMediaStatus;
use App\Models\AuditLog;
use App\Models\ContentMediaAsset;
use App\Models\PetProfile;
use App\Models\PetProfileMedia;
use App\Models\User;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use App\Services\PetProfileEventRecorder;
use App\Validation\PetProfileMediaRules;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final readonly class StorePetPrimaryPhoto
{
    public function __construct(
        private ForumActor $actor,
        private Gate $gate,
        private PetProfileAccess $access,
        private PetProfileEventRecorder $events,
        private StorePrivateImage $images,
    ) {}

    public function handle(
        PetProfile $profile,
        UploadedFile $upload,
        string $altText,
        string $idempotencyKey,
    ): PetProfileMedia {
        $user = $this->actor->requireUser();
        $this->gate->authorize('manageMedia', $profile);
        Validator::make([
            'upload' => $upload,
            'alt_text' => $altText,
            'idempotency_key' => $idempotencyKey,
        ], [
            'upload' => PetProfileMediaRules::upload(),
            'alt_text' => PetProfileMediaRules::altText(),
            'idempotency_key' => PetProfileMediaRules::idempotencyKey(),
        ])->validate();

        $uploadKey = $this->uploadKey($profile, $user, $idempotencyKey);
        $existing = PetProfileMedia::query()
            ->with('asset')
            ->where('upload_key', $uploadKey)
            ->first();

        if ($existing instanceof PetProfileMedia) {
            return $this->validateReplay($existing, $profile, $user);
        }

        $path = $this->images->handle(
            $upload,
            "pet-profiles/{$profile->profile_key}/media",
            'upload',
        );
        $stored = $this->storedMetadata($path);
        $wasReplay = false;

        try {
            $media = DB::transaction(function () use (
                $altText,
                $path,
                $profile,
                $stored,
                $upload,
                $uploadKey,
                $user,
                &$wasReplay,
            ): PetProfileMedia {
                $lockedProfile = PetProfile::query()
                    ->select(['id', 'user_id', 'profile_key', 'status', 'lock_version'])
                    ->lockForUpdate()
                    ->findOrFail($profile->id);
                $this->gate->forUser($user)->authorize('manageMedia', $lockedProfile);
                $existing = PetProfileMedia::query()
                    ->with('asset')
                    ->where('upload_key', $uploadKey)
                    ->first();

                if ($existing instanceof PetProfileMedia) {
                    $wasReplay = true;

                    return $this->validateReplay($existing, $lockedProfile, $user);
                }

                $current = PetProfileMedia::query()
                    ->where('pet_profile_id', $lockedProfile->id)
                    ->where('current_key', "primary:{$lockedProfile->id}")
                    ->lockForUpdate()
                    ->first();

                if ($current instanceof PetProfileMedia) {
                    $current->forceFill([
                        'status' => PetProfileMediaStatus::Superseded,
                        'current_key' => null,
                        'recoverable_until' => now()->addDays(
                            config('images.pet_profile_uploads.recovery_days', 30),
                        ),
                        'replaced_at' => now(),
                    ])->save();
                }

                $asset = ContentMediaAsset::query()->create([
                    'media_key' => (string) Str::ulid(),
                    'owner_user_id' => $user->id,
                    'created_by_user_id' => $user->id,
                    'media_type' => ContentMediaType::Image,
                    'status' => ContentMediaStatus::Ready,
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $this->originalName($upload),
                    'mime_type' => 'image/webp',
                    'byte_size' => $stored['byte_size'],
                    'checksum_sha256' => $stored['checksum'],
                    'alt_text' => trim($altText),
                    'licence' => null,
                    'safe_metadata' => [
                        'gps_removed' => true,
                        'orientation_normalized' => true,
                        'width' => $stored['width'],
                        'height' => $stored['height'],
                    ],
                    'retained_until' => null,
                ]);
                $media = PetProfileMedia::query()->create([
                    'media_key' => (string) Str::ulid(),
                    'pet_profile_id' => $lockedProfile->id,
                    'content_media_asset_id' => $asset->id,
                    'attached_by_user_id' => $user->id,
                    'role' => 'primary',
                    'status' => PetProfileMediaStatus::Active,
                    'current_key' => "primary:{$lockedProfile->id}",
                    'upload_key' => $uploadKey,
                ]);
                $lockedProfile->increment('lock_version');
                $manager = $this->access->membership($lockedProfile, $user);
                $this->events->record(
                    profile: $lockedProfile,
                    actor: $user,
                    eventType: 'primary-photo-updated',
                    reasonCode: 'primary-photo-updated',
                    publicMetadata: [
                        'media_key' => $media->media_key,
                        'replaced_existing' => $current instanceof PetProfileMedia,
                    ],
                    idempotencyKey: "pet-photo-store:{$uploadKey}",
                    manager: $manager,
                );
                $this->audit($lockedProfile, $user, $manager?->role->value, 'updated', $media);

                return $media->setRelation('asset', $asset);
            }, 3);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        if ($wasReplay) {
            Storage::disk('local')->delete($path);
        }

        return $media;
    }

    /** @return array{byte_size: int, checksum: string, width: int, height: int} */
    private function storedMetadata(string $path): array
    {
        $absolutePath = Storage::disk('local')->path($path);
        $dimensions = @getimagesize($absolutePath);
        $byteSize = @filesize($absolutePath);
        $checksum = @hash_file('sha256', $absolutePath);

        if ($dimensions === false || ! is_int($byteSize) || ! is_string($checksum)) {
            Storage::disk('local')->delete($path);

            throw ValidationException::withMessages([
                'upload' => __('pet_profiles.validation.photo_processing'),
            ]);
        }

        return [
            'byte_size' => $byteSize,
            'checksum' => $checksum,
            'width' => (int) $dimensions[0],
            'height' => (int) $dimensions[1],
        ];
    }

    private function originalName(UploadedFile $upload): string
    {
        $name = basename(str_replace('\\', '/', $upload->getClientOriginalName()));

        return Str::limit($name !== '' ? $name : 'pet-photo', 255, '');
    }

    private function uploadKey(PetProfile $profile, User $user, string $idempotencyKey): string
    {
        return hash('sha256', "pet-primary-photo|{$profile->id}|{$user->id}|{$idempotencyKey}");
    }

    private function validateReplay(
        PetProfileMedia $media,
        PetProfile $profile,
        User $user,
    ): PetProfileMedia {
        if ($media->pet_profile_id !== $profile->id || $media->attached_by_user_id !== $user->id) {
            throw ValidationException::withMessages([
                'idempotency_key' => __('pet_profiles.validation.photo_idempotency_conflict'),
            ]);
        }

        return $media;
    }

    private function audit(
        PetProfile $profile,
        User $user,
        ?string $managerRole,
        string $action,
        PetProfileMedia $media,
    ): void {
        AuditLog::query()->create([
            'actor_key' => $user->actor_key,
            'actor_role' => $managerRole ?? 'profile-manager',
            'action' => "pet-profile.primary-photo-{$action}",
            'target_type' => PetProfile::class,
            'target_id' => (string) $profile->id,
            'metadata' => ['media_key' => $media->media_key],
        ]);
    }
}
