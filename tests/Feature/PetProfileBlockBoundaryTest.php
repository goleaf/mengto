<?php

declare(strict_types=1);

use App\Actions\StorePetPrimaryPhoto;
use App\Enums\PetProfileStatus;
use App\Enums\SocialRelationshipType;
use App\Models\PetProfile;
use App\Models\SocialAccountBlock;
use App\Models\SocialRelationship;
use App\Models\User;
use App\Services\SocialActorResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('account and actor blocks hide direct public pet profiles and media', function (string $blockType): void {
    Storage::fake('local');
    $owner = User::factory()->create(['name' => 'Pet owner']);
    $viewer = User::factory()->create(['name' => 'Blocked viewer']);
    $profile = PetProfile::factory()->for($owner)->create([
        'name' => 'Visible before block',
        'visibility' => 'public',
        'status' => PetProfileStatus::Active,
        'is_discoverable' => true,
    ]);
    $resolver = app(SocialActorResolver::class);
    $viewerActor = $resolver->forUser($viewer);
    $petActor = $resolver->forPet($profile);

    $this->actingAs($owner);
    $media = app(StorePetPrimaryPhoto::class)->handle(
        $profile,
        UploadedFile::fake()->image('profile.jpg', 200, 200),
        'A profile image.',
        "pet-block-boundary-{$blockType}",
    );
    $profileUrl = route('pets.profile', $profile);
    $mediaUrl = route('pets.media.show', [
        'petProfile' => $profile,
        'petProfileMedia' => $media,
    ]);

    $this->actingAs($viewer);
    $this->get($profileUrl)->assertOk();
    $this->get($mediaUrl)->assertOk();

    if ($blockType === 'account') {
        SocialAccountBlock::factory()->create([
            'blocker_user_id' => $viewer->id,
            'blocked_user_id' => $owner->id,
            'created_by_user_id' => $viewer->id,
        ]);
    } else {
        SocialRelationship::factory()->create([
            'source_actor_id' => $petActor->id,
            'target_actor_id' => $viewerActor->id,
            'relationship_type' => SocialRelationshipType::Block,
            'created_by_user_id' => $owner->id,
        ]);
    }

    $this->get($profileUrl)->assertNotFound();
    $this->get($mediaUrl)->assertNotFound();
})->with(['account', 'actor']);
