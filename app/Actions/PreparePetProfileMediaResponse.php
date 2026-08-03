<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ContentMediaStatus;
use App\Enums\ContentMediaType;
use App\Enums\PetProfileMediaStatus;
use App\Models\ContentMediaAsset;
use App\Models\PetProfile;
use App\Models\PetProfileMedia;
use App\Models\User;
use App\Services\PrivateFileResponse;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class PreparePetProfileMediaResponse
{
    public function __construct(
        private AuthFactory $auth,
        private Gate $gate,
        private PrivateFileResponse $files,
    ) {}

    public function handle(
        PetProfile $profile,
        PetProfileMedia $media,
    ): StreamedResponse {
        abort_unless($media->pet_profile_id === $profile->id, 404);
        $media->loadMissing('asset');
        $asset = $media->asset;
        abort_unless($this->canView($profile, $media), 404);
        abort_unless(
            $asset instanceof ContentMediaAsset
                && $asset->media_type === ContentMediaType::Image
                && $asset->status === ContentMediaStatus::Ready
                && $asset->disk === 'local'
                && $asset->mime_type === 'image/webp',
            404,
        );

        return $this->files->inline(
            disk: $asset->disk,
            path: $asset->path,
            allowedDirectory: "pet-profiles/{$profile->profile_key}/media",
            headers: [
                'Cache-Control' => 'no-store, private',
                'Content-Type' => 'image/webp',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    private function canView(PetProfile $profile, PetProfileMedia $media): bool
    {
        if ($media->status === PetProfileMediaStatus::Active
            && $media->current_key === "primary:{$profile->id}"
            && $this->gate->allows('view', $profile)
        ) {
            return true;
        }

        $user = $this->auth->guard('web')->user();

        return $user instanceof User
            && in_array($media->status, [
                PetProfileMediaStatus::Superseded,
                PetProfileMediaStatus::Removed,
            ], true)
            && $media->recoverable_until?->isFuture() === true
            && $this->gate->forUser($user)->allows('manageMedia', $profile);
    }
}
