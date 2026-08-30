<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceMediaStatus;
use App\Enums\PlaceMediaVariant;
use App\Enums\PlaceMediaVariantStatus;
use App\Models\Place;
use App\Models\PlaceMedia;
use App\Models\PlaceMediaVariant as PlaceMediaVariantModel;
use App\Models\User;
use App\Services\PrivateFileResponse;
use Illuminate\Contracts\Auth\Access\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class PreparePlaceMediaResponse
{
    public function __construct(private Gate $gate, private PrivateFileResponse $files) {}

    public function handle(
        User $actor,
        Place $place,
        PlaceMedia $media,
        PlaceMediaVariant $variant,
    ): StreamedResponse {
        abort_unless($media->place_id === $place->id, 404);
        $media->loadMissing(['place.organization.activeMemberships', 'asset', 'variants']);
        abort_unless($this->gate->forUser($actor)->allows('view', $media), 404);
        abort_unless($media->status === PlaceMediaStatus::Active, 404);
        $selected = $media->variants->firstWhere('variant', $variant);
        abort_unless(
            $selected instanceof PlaceMediaVariantModel
                && $selected->status === PlaceMediaVariantStatus::Ready
                && $selected->disk === 'local'
                && $selected->mime_type === 'image/webp',
            404,
        );

        return $this->files->inline(
            disk: $selected->disk,
            path: $selected->path,
            allowedDirectory: "place-media/{$place->stable_key}/{$media->media_key}",
            headers: [
                'Cache-Control' => 'private, no-store',
                'Content-Type' => 'image/webp',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "default-src 'none'; sandbox",
            ],
        );
    }
}
