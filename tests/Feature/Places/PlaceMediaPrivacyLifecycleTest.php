<?php

declare(strict_types=1);

use App\Actions\PreparePlaceMediaResponse;
use App\Actions\RemovePlaceMedia;
use App\Actions\ReorderPlaceMedia;
use App\Actions\ReviewPlaceMedia;
use App\Actions\StorePlaceMedia;
use App\Enums\PlaceMediaStatus;
use App\Enums\PlaceMediaVariant;
use App\Models\Place;
use App\Models\PlaceMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    Storage::fake('local');
    Storage::fake('public');
});

test('manager image upload stages privately and stores safe oriented optimized derivatives', function (): void {
    $place = Place::factory()->for($this->authenticatedUser, 'owner')->create();
    $upload = UploadedFile::fake()->image('park-entrance.jpg', 1600, 1200)->size(900);

    $media = app(StorePlaceMedia::class)->handle(
        $this->authenticatedUser,
        $place,
        $upload,
        'Tree-lined approximate public entrance.',
        'Photograph by the place manager',
        'all-rights-reserved',
        'place-media-upload-000000000001',
    );

    $media->load(['asset', 'variants']);

    expect($media->status)->toBe(PlaceMediaStatus::PendingReview)
        ->and($media->variants)->toHaveCount(4)
        ->and($media->variants->pluck('variant')->all())->toEqualCanonicalizing([
            PlaceMediaVariant::Fallback,
            PlaceMediaVariant::Small,
            PlaceMediaVariant::Medium,
            PlaceMediaVariant::Large,
        ])
        ->and($media->asset->mime_type)->toBe('image/webp')
        ->and($media->asset->safe_metadata)->toMatchArray([
            'gps_removed' => true,
            'orientation_normalized' => true,
        ])
        ->and(Storage::disk('public')->allFiles())->toBeEmpty()
        ->and(collect(Storage::disk('local')->allFiles())->contains(
            static fn (string $path): bool => str_contains($path, '/staging/'),
        ))->toBeFalse();

    foreach ($media->variants as $variant) {
        Storage::disk('local')->assertExists($variant->getRawOriginal('path'));
        $dimensions = getimagesizefromstring(Storage::disk('local')->get($variant->getRawOriginal('path')));

        expect($dimensions)->not->toBeFalse()
            ->and($dimensions['mime'])->toBe('image/webp')
            ->and($dimensions[0])->toBeLessThanOrEqual(1200)
            ->and($dimensions[1])->toBeLessThanOrEqual(900);
    }
});

test('place upload rejects forged image content and leaves both disks empty', function (): void {
    $place = Place::factory()->for($this->authenticatedUser, 'owner')->create();
    $upload = UploadedFile::fake()->createWithContent('forged.jpg', '<script>alert(1)</script>');

    expect(fn () => app(StorePlaceMedia::class)->handle(
        $this->authenticatedUser,
        $place,
        $upload,
        'A truthful description',
        'Place manager',
        'all-rights-reserved',
        'place-media-upload-000000000002',
    ))->toThrow(ValidationException::class)
        ->and(Storage::disk('local')->allFiles())->toBeEmpty()
        ->and(Storage::disk('public')->allFiles())->toBeEmpty()
        ->and(PlaceMedia::query()->count())->toBe(0);
});

test('only approved media is delivered through its authorized private response', function (): void {
    $place = Place::factory()->public()->for($this->authenticatedUser, 'owner')->create();
    $media = storePlaceTestMedia($this->authenticatedUser, $place, 'delivery');
    $viewer = User::factory()->create();

    expect(fn () => app(PreparePlaceMediaResponse::class)->handle(
        $viewer,
        $place,
        $media,
        PlaceMediaVariant::Small,
    ))->toThrow(Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

    $moderator = User::factory()->create(['is_admin' => true]);
    $approved = app(ReviewPlaceMedia::class)->handle(
        $moderator,
        $place,
        $media,
        true,
        'content-approved',
        'place-media-review-000000000001',
    );
    $response = app(PreparePlaceMediaResponse::class)->handle(
        $viewer,
        $place,
        $approved,
        PlaceMediaVariant::Small,
    );

    expect($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and($response->headers->get('Content-Type'))->toBe('image/webp')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');

    $foreign = Place::factory()->public()->create();
    expect(fn () => app(PreparePlaceMediaResponse::class)->handle(
        $viewer,
        $foreign,
        $approved,
        PlaceMediaVariant::Small,
    ))->toThrow(Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
});

test('media ordering removal and retention are locked and non public immediately', function (): void {
    $place = Place::factory()->for($this->authenticatedUser, 'owner')->create();
    $first = storePlaceTestMedia($this->authenticatedUser, $place, 'first');
    $second = storePlaceTestMedia($this->authenticatedUser, $place, 'second');

    app(ReorderPlaceMedia::class)->handle(
        $this->authenticatedUser,
        $place,
        [$second->media_key, $first->media_key],
        'place-media-reorder-0000000001',
    );

    expect($second->fresh()->position)->toBe(1)
        ->and($first->fresh()->position)->toBe(2);

    $removed = app(RemovePlaceMedia::class)->handle(
        $this->authenticatedUser,
        $place,
        $first,
        'manager-removed',
        'place-media-remove-00000000001',
    );

    expect($removed->status)->toBe(PlaceMediaStatus::Removed)
        ->and($removed->recoverable_until)->not->toBeNull()
        ->and($removed->recoverable_until->isAfter(now()->addDays(29)))->toBeTrue();
});

function storePlaceTestMedia(User $actor, Place $place, string $suffix): PlaceMedia
{
    return app(StorePlaceMedia::class)->handle(
        $actor,
        $place,
        UploadedFile::fake()->image("{$suffix}.jpg", 1200, 900)->size(500),
        "Accessible {$suffix} image description",
        'Place manager attribution',
        'all-rights-reserved',
        "place-media-upload-{$suffix}-000000000001",
    );
}
