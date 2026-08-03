<?php

declare(strict_types=1);

use App\Actions\RemovePetPrimaryPhoto;
use App\Actions\RestorePetPrimaryPhoto;
use App\Actions\StorePetPrimaryPhoto;
use App\Enums\ContentMediaStatus;
use App\Enums\ContentMediaType;
use App\Enums\PetProfileMediaStatus;
use App\Livewire\Pets\CreatePetProfile;
use App\Livewire\Pets\ManagePetProfile;
use App\Models\ContentMediaAsset;
use App\Models\PetProfile;
use App\Models\PetProfileMedia;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates a consistent active placement from factory defaults', function (): void {
    $placement = PetProfileMedia::factory()->create();

    expect($placement->status)->toBe(PetProfileMediaStatus::Active)
        ->and($placement->current_key)->toBe("primary:{$placement->pet_profile_id}")
        ->and($placement->profile()->exists())->toBeTrue()
        ->and($placement->asset()->exists())->toBeTrue()
        ->and(Schema::hasIndex(
            'pet_profile_media',
            'pet_profile_media_attached_by_idx',
        ))->toBeTrue();
});

it('stores the optional creation portrait as canonical private media', function (): void {
    Storage::fake('local');
    $owner = User::factory()->create();
    $this->actingAs($owner);

    $component = Livewire::test(CreatePetProfile::class)
        ->set('form.name', 'Baks')
        ->set('form.species', 'dog')
        ->set('form.relationshipRole', 'primary-owner')
        ->set('form.visibility', 'private')
        ->set(
            'mediaForm.upload',
            UploadedFile::fake()->image('Baks portrait.png', 3200, 2000),
        )
        ->set('mediaForm.altText', 'Baks sitting beside a blue blanket.')
        ->call('create')
        ->assertHasNoErrors();

    $profile = PetProfile::query()->sole();
    $placement = PetProfileMedia::query()->with('asset')->sole();
    $asset = $placement->asset;
    $storedImage = getimagesizefromstring(Storage::disk('local')->get($asset->path));

    $component->assertRedirect(route('pets.manage.show', [
        'petProfile' => $profile->profile_key,
    ]));

    expect(Schema::hasTable('pet_profile_media'))->toBeTrue()
        ->and($placement->pet_profile_id)->toBe($profile->id)
        ->and($placement->status)->toBe(PetProfileMediaStatus::Active)
        ->and($placement->current_key)->toBe("primary:{$profile->id}")
        ->and($asset->owner_user_id)->toBe($owner->id)
        ->and($asset->created_by_user_id)->toBe($owner->id)
        ->and($asset->media_type)->toBe(ContentMediaType::Image)
        ->and($asset->status)->toBe(ContentMediaStatus::Ready)
        ->and($asset->disk)->toBe('local')
        ->and($asset->original_name)->toBe('Baks portrait.png')
        ->and($asset->path)->toStartWith("pet-profiles/{$profile->profile_key}/media/")
        ->and($asset->path)->toEndWith('.webp')
        ->and($asset->path)->not->toContain('Baks')
        ->and($asset->alt_text)->toBe('Baks sitting beside a blue blanket.')
        ->and($asset->safe_metadata)->toMatchArray([
            'gps_removed' => true,
            'orientation_normalized' => true,
            'width' => 2560,
            'height' => 1600,
        ])
        ->and($storedImage)->not->toBeFalse()
        ->and($storedImage[0])->toBe(2560)
        ->and($storedImage[1])->toBe(1600)
        ->and($storedImage['mime'])->toBe('image/webp');

    Storage::disk('local')->assertExists($asset->path);
});

it('validates image content and manage-media permission inside the action', function (): void {
    Storage::fake('local');
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create([
        'visibility' => 'private',
        'is_discoverable' => false,
    ]);

    $this->actingAs($outsider);

    expect(fn () => app(StorePetPrimaryPhoto::class)->handle(
        $profile,
        UploadedFile::fake()->image('foreign.png'),
        'A foreign upload.',
        'pet-photo-authorization-test',
    ))->toThrow(AuthorizationException::class);

    $this->actingAs($owner);

    expect(fn () => app(StorePetPrimaryPhoto::class)->handle(
        $profile,
        UploadedFile::fake()->createWithContent('broken.jpg', '<?php echo "unsafe";'),
        'Malformed content.',
        'pet-photo-content-test',
    ))->toThrow(ValidationException::class);

    expect(ContentMediaAsset::query()->count())->toBe(0)
        ->and(PetProfileMedia::query()->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBeEmpty();
});

it('replays uploads idempotently and retains replaced media for recovery', function (): void {
    Storage::fake('local');
    $owner = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create();
    $this->actingAs($owner);
    $action = app(StorePetPrimaryPhoto::class);

    $first = $action->handle(
        $profile,
        UploadedFile::fake()->image('first.jpg', 800, 600),
        'The first portrait.',
        'pet-photo-upload-first',
    );
    $replayed = $action->handle(
        $profile,
        UploadedFile::fake()->image('ignored.jpg', 600, 800),
        'Ignored replay.',
        'pet-photo-upload-first',
    );
    $second = $action->handle(
        $profile->refresh(),
        UploadedFile::fake()->image('second.png', 900, 900),
        'The replacement portrait.',
        'pet-photo-upload-second',
    );

    expect($replayed->is($first))->toBeTrue()
        ->and($first->refresh()->status)->toBe(PetProfileMediaStatus::Superseded)
        ->and($first->current_key)->toBeNull()
        ->and($first->recoverable_until)->not->toBeNull()
        ->and($first->recoverable_until?->isFuture())->toBeTrue()
        ->and($second->status)->toBe(PetProfileMediaStatus::Active)
        ->and($second->current_key)->toBe("primary:{$profile->id}")
        ->and(PetProfileMedia::query()->count())->toBe(2)
        ->and(ContentMediaAsset::query()->count())->toBe(2)
        ->and(Storage::disk('local')->allFiles())->toHaveCount(2)
        ->and($profile->lifecycleEvents()
            ->where('event_type', 'primary-photo-updated')
            ->count())->toBe(2);
});

it('removes and restores a primary portrait without destroying its private file', function (): void {
    Storage::fake('local');
    $owner = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create([
        'visibility' => 'private',
        'is_discoverable' => false,
    ]);
    $this->actingAs($owner);
    $media = app(StorePetPrimaryPhoto::class)->handle(
        $profile,
        UploadedFile::fake()->image('recoverable.jpg', 720, 720),
        'A recoverable portrait.',
        'pet-photo-recoverable-upload',
    );
    $path = $media->asset()->firstOrFail()->path;

    app(RemovePetPrimaryPhoto::class)->handle(
        $profile->refresh(),
        'pet-photo-remove-first',
    );
    app(RemovePetPrimaryPhoto::class)->handle(
        $profile->refresh(),
        'pet-photo-remove-first',
    );

    expect($media->refresh()->status)->toBe(PetProfileMediaStatus::Removed)
        ->and($media->current_key)->toBeNull()
        ->and($media->recoverable_until?->isFuture())->toBeTrue()
        ->and($profile->primaryMedia()->exists())->toBeFalse()
        ->and($profile->lifecycleEvents()
            ->where('event_type', 'primary-photo-removed')
            ->count())->toBe(1);
    Storage::disk('local')->assertExists($path);

    app(RestorePetPrimaryPhoto::class)->handle(
        $profile->refresh(),
        $media->refresh(),
        'pet-photo-restore-first',
    );

    expect($media->refresh()->status)->toBe(PetProfileMediaStatus::Active)
        ->and($media->current_key)->toBe("primary:{$profile->id}")
        ->and($media->recoverable_until)->toBeNull()
        ->and($profile->lifecycleEvents()
            ->where('event_type', 'primary-photo-restored')
            ->count())->toBe(1);
    Storage::disk('local')->assertExists($path);
});

it('serves only policy-visible private pet media and never exposes storage metadata', function (): void {
    Storage::fake('local');
    $owner = User::factory()->create();
    $viewer = User::factory()->create(['email_verified_at' => now()]);
    $profile = PetProfile::factory()->for($owner)->create([
        'visibility' => 'public',
        'is_discoverable' => true,
    ]);
    $this->actingAs($owner);
    $media = app(StorePetPrimaryPhoto::class)->handle(
        $profile,
        UploadedFile::fake()->image('public-portrait.jpg', 800, 800),
        'Baks looking toward the camera.',
        'pet-photo-public-upload',
    );
    $asset = $media->asset()->firstOrFail();
    $url = route('pets.media.show', [
        'petProfile' => $profile->profile_key,
        'petProfileMedia' => $media->media_key,
    ]);
    $contents = Storage::disk('local')->get($asset->path);

    $this->actingAs($viewer);
    $this->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'image/webp')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertStreamedContent($contents);
    $this->get(route('pets.profile', ['petProfile' => $profile->profile_key]))
        ->assertOk()
        ->assertSee($url, false)
        ->assertDontSee($asset->path)
        ->assertDontSee($asset->checksum_sha256);

    $profile->forceFill([
        'visibility' => 'private',
        'is_discoverable' => false,
    ])->save();

    $this->get($url)->assertNotFound();
    $this->actingAs($owner)->get($url)->assertOk();
});

it('rejects pet media records whose private path escapes the owning profile directory', function (): void {
    Storage::fake('local');
    $owner = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create();
    $this->actingAs($owner);
    $media = app(StorePetPrimaryPhoto::class)->handle(
        $profile,
        UploadedFile::fake()->image('portrait.jpg', 100, 100),
        'A valid portrait.',
        'pet-photo-path-upload',
    );
    $asset = $media->asset()->firstOrFail();
    Storage::disk('local')->put('pet-profiles/foreign/media/private.webp', 'foreign');
    $asset->forceFill(['path' => 'pet-profiles/foreign/media/private.webp'])->save();

    $this->get(route('pets.media.show', [
        'petProfile' => $profile->profile_key,
        'petProfileMedia' => $media->media_key,
    ]))->assertNotFound();
});

it('exposes the complete photo workflow in the authenticated management workspace', function (): void {
    Storage::fake('local');
    $owner = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create([
        'visibility' => 'private',
        'is_discoverable' => false,
    ]);

    Livewire::actingAs($owner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSeeHtml('id="pet-media"')
        ->set(
            'mediaForm.upload',
            UploadedFile::fake()->image('workspace-photo.jpg', 640, 480),
        )
        ->set('mediaForm.altText', 'Baks resting on the garden path.')
        ->call('replacePrimaryPhoto')
        ->assertHasNoErrors()
        ->assertSee(__('pet_profiles.feedback.photo_saved'));

    $media = PetProfileMedia::query()->sole();

    Livewire::actingAs($owner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile->refresh()])
        ->assertSee(route('pets.media.show', [
            'petProfile' => $profile->profile_key,
            'petProfileMedia' => $media->media_key,
        ]), false)
        ->call('removePrimaryPhoto')
        ->assertHasNoErrors()
        ->assertSee(__('pet_profiles.feedback.photo_removed'));

    Livewire::actingAs($owner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile->refresh()])
        ->assertSee(__('pet_profiles.media.recovery_title'))
        ->call('restorePrimaryPhoto', $media->media_key)
        ->assertHasNoErrors()
        ->assertSee(__('pet_profiles.feedback.photo_restored'));
});
