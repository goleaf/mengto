<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\CreateContentPublication;
use App\Data\CreateContentPublicationData;
use App\Enums\ContentAudienceType;
use App\Enums\ContentPublicationStatus;
use App\Enums\ContentPublicationType;
use App\Enums\PetEvidenceStatus;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Services\SocialActorResolver;
use Illuminate\Database\Seeder;
use LogicException;

final class DiscoveryDemoSeeder extends Seeder
{
    public function run(
        SocialActorResolver $actors,
        CreateContentPublication $createPublication,
    ): void {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException(__('member_profiles.errors.demo_seed_environment'));
        }

        $member = User::query()
            ->select(['id', 'actor_key', 'name', 'status'])
            ->where('actor_key', 'demo-lithuanian')
            ->firstOrFail();
        $mia = User::query()
            ->select(['id', 'actor_key', 'name', 'status'])
            ->where('actor_key', 'mia-carter')
            ->firstOrFail();
        $actor = $actors->forUser($member);
        $actor->forceFill(['is_discoverable' => true])->saveQuietly();
        $actor->settings()->update(['is_recommendable' => true]);
        $pet = PetProfile::query()->updateOrCreate(
            ['profile_key' => 'discovery-demo-pet'],
            [
                'user_id' => $member->id,
                'slug' => 'atradimu-draugas',
                'name' => 'Meta',
                'species' => 'Dog',
                'breed' => 'Mixed breed',
                'birth_date' => '2021-05-12',
                'birth_date_precision' => 'exact',
                'visibility' => 'public',
                'status' => 'active',
                'is_discoverable' => true,
                'published_at' => '2026-08-01 12:00:00',
            ],
        );
        $petActor = $actors->forPet($pet);
        $petActor->forceFill(['is_discoverable' => true])->saveQuietly();
        $petActor->settings()->update(['is_recommendable' => true]);

        PetProfileManager::query()->updateOrCreate(
            [
                'pet_profile_id' => $pet->id,
                'user_id' => $mia->id,
            ],
            [
                'actor_key_snapshot' => $mia->actor_key,
                'role' => PetManagerRole::Caregiver,
                'status' => PetManagerStatus::Invited,
                'permission_overrides' => null,
                'evidence_status' => PetEvidenceStatus::Unverified,
                'starts_at' => now(),
                'ends_at' => now()->addDays(14),
                'accepted_at' => null,
                'revoked_at' => null,
                'invited_by_user_id' => $member->id,
                'lock_version' => 1,
                'metadata' => ['scenario' => 'pet-workspace-shared-access'],
            ],
        );

        $createPublication->handle(
            $member,
            $actor,
            new CreateContentPublicationData(
                type: ContentPublicationType::Post,
                status: ContentPublicationStatus::Published,
                audience: ContentAudienceType::Registered,
                language: 'en',
                idempotencyKey: 'discovery-demo-post-v1',
                title: 'Three calm checks before a community meetup',
                summary: 'A practical preparation note for handlers planning a low-pressure first meeting.',
                body: 'Confirm the route, bring water, and agree on distance before introducing the animals.',
            ),
        );
    }
}
