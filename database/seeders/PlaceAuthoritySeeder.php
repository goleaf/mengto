<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\CreatePlace;
use App\Data\CreatePlaceData;
use App\Enums\PlaceAccessGrantStatus;
use App\Enums\PlaceAccessibilityStatus;
use App\Enums\PlaceAccessPurpose;
use App\Enums\PlaceType;
use App\Enums\PlaceVerificationStatus;
use App\Enums\PlaceVisibility;
use App\Enums\VenueAreaType;
use App\Enums\VenueStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventRoom;
use App\Models\ForumEventVersion;
use App\Models\Organization;
use App\Models\Place;
use App\Models\PlaceAccessAudit;
use App\Models\PlaceAccessGrant;
use App\Models\PlaceLocationVersion;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueArea;
use App\Services\ForumEventLifecycleSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

final class PlaceAuthoritySeeder extends Seeder
{
    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Place demo data requires an explicitly allowed environment.');
        }

        $mia = User::query()->where('actor_key', 'mia-carter')->firstOrFail();
        $participant = User::query()->where('actor_key', 'demo-lithuanian')->firstOrFail();
        $administrator = User::query()->where('actor_key', 'demo-administrator')->firstOrFail();
        $organization = Organization::query()
            ->where('stable_key', 'demo-organization-vilnius-welfare')
            ->firstOrFail();

        $public = $this->publicPlace($mia);
        $publicVenue = $this->venue($public, null, 80, 30);
        $this->area(
            $publicVenue,
            'vingis-quiet-rest-area',
            'Quiet animal rest area',
            VenueAreaType::QuietArea,
            12,
            8,
            false,
        );
        $organizationPlace = $this->organizationPlace($mia, $organization);
        $organizationVenue = $this->venue($organizationPlace, $organization, 140, 35);
        $organizationHall = $this->area(
            $organizationVenue,
            'welfare-network-main-hall',
            'Welfare network main hall',
            VenueAreaType::MainHall,
            120,
            20,
            true,
        );
        $foster = $this->privateFosterPlace($mia, $organization);

        PlaceLocationVersion::query()->updateOrCreate(
            ['place_id' => $foster->id, 'version' => 1],
            [
                'changed_by_user_id' => $mia->id,
                'public_region' => $foster->public_region,
                'public_address' => null,
                'public_latitude' => $foster->public_latitude,
                'public_longitude' => $foster->public_longitude,
                'exact_address' => $foster->exact_address,
                'exact_latitude' => $foster->exact_latitude,
                'exact_longitude' => $foster->exact_longitude,
                'private_instructions' => $foster->private_instructions,
                'reason_code' => 'demo-authoritative-location',
                'created_at' => now(),
            ],
        );

        $this->linkEvent('demo-point13-community-meetup', $public, $publicVenue, $administrator);
        $conference = $this->linkEvent(
            'demo-point13-care-conference',
            $organizationPlace,
            $organizationVenue,
            $administrator,
        );
        $controlledIntroduction = $this->linkEvent(
            'demo-point13-controlled-introduction',
            $foster,
            null,
            $administrator,
        );

        if ($conference !== null) {
            ForumEventRoom::query()
                ->where('forum_event_id', $conference->id)
                ->where('stable_key', 'demo-point13-conference-main-room')
                ->update(['venue_area_id' => $organizationHall->id]);
        }

        $activeGrant = PlaceAccessGrant::query()->updateOrCreate(
            ['idempotency_key' => 'demo-place-access-controlled-introduction'],
            [
                'place_id' => $foster->id,
                'user_id' => $participant->id,
                'event_id' => $controlledIntroduction?->id,
                'issued_by_user_id' => $mia->id,
                'revoked_by_user_id' => null,
                'purpose' => PlaceAccessPurpose::EventOperations,
                'status' => PlaceAccessGrantStatus::Active,
                'may_view_exact_location' => true,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addDays(14),
                'revoked_at' => null,
                'revocation_reason_code' => null,
            ],
        );
        PlaceAccessGrant::query()->updateOrCreate(
            ['idempotency_key' => 'demo-place-access-expired-professional'],
            [
                'place_id' => $foster->id,
                'user_id' => $administrator->id,
                'event_id' => null,
                'issued_by_user_id' => $mia->id,
                'purpose' => PlaceAccessPurpose::ProfessionalVisit,
                'status' => PlaceAccessGrantStatus::Expired,
                'may_view_exact_location' => true,
                'valid_from' => now()->subMonth(),
                'valid_until' => now()->subWeeks(2),
                'revoked_at' => null,
                'revocation_reason_code' => null,
            ],
        );
        PlaceAccessGrant::query()->updateOrCreate(
            ['idempotency_key' => 'demo-place-access-revoked-operations'],
            [
                'place_id' => $foster->id,
                'user_id' => $administrator->id,
                'event_id' => $controlledIntroduction?->id,
                'issued_by_user_id' => $mia->id,
                'revoked_by_user_id' => $mia->id,
                'purpose' => PlaceAccessPurpose::EventOperations,
                'status' => PlaceAccessGrantStatus::Revoked,
                'may_view_exact_location' => true,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addWeek(),
                'revoked_at' => now(),
                'revocation_reason_code' => 'demo-staff-removed',
            ],
        );
        PlaceAccessAudit::query()->firstOrCreate(
            [
                'place_id' => $foster->id,
                'user_id' => $participant->id,
                'event_type' => 'exact-location-viewed',
                'channel' => 'demo-event-workspace',
            ],
            [
                'place_access_grant_id' => $activeGrant->id,
                'event_id' => $controlledIntroduction?->id,
                'purpose' => PlaceAccessPurpose::EventOperations->value,
                'created_at' => now(),
            ],
        );
    }

    private function publicPlace(User $actor): Place
    {
        $place = Place::query()->where('stable_key', 'vingis-quiet-loop')->first();

        if ($place === null) {
            $place = app(CreatePlace::class)->handle($actor, new CreatePlaceData(
                name: 'Vingis Quiet Loop',
                type: PlaceType::WalkingRoute,
                visibility: PlaceVisibility::Public,
                publicRegion: 'Vilnius',
                publicAddress: 'Vingis Park public entrance',
                exactAddress: 'Vingis Park public entrance',
                publicLatitude: '54.683900',
                publicLongitude: '25.237900',
                exactLatitude: '54.683912',
                exactLongitude: '25.237912',
                locale: 'en',
                idempotencyKey: 'demo-place-vingis-quiet-loop-0001',
                summary: 'A public, low-noise walking loop with rest and water points.',
                petRules: 'Leashes are required beside shared cycle paths.',
                verificationStatus: PlaceVerificationStatus::Verified,
                accessibilityStatus: PlaceAccessibilityStatus::PartiallyAccessible,
            ));
        }
        $place->forceFill([
            'stable_key' => 'vingis-quiet-loop',
            'slug' => 'vingis-quiet-loop',
            'verification_source' => 'demo-city-open-data',
            'verified_at' => now(),
            'information_expires_at' => now()->addYear(),
            'accessibility_facts' => ['paved_sections', 'rest_seating'],
        ])->save();

        return $place;
    }

    private function organizationPlace(User $actor, Organization $organization): Place
    {
        $place = app(CreatePlace::class)->handle($actor, new CreatePlaceData(
            name: 'Vilnius Welfare Community Centre',
            type: PlaceType::OrganizationLocation,
            visibility: PlaceVisibility::Public,
            publicRegion: 'Vilnius Old Town',
            publicAddress: 'Community Street 12, Vilnius',
            exactAddress: 'Community Street 12, Vilnius',
            publicLatitude: '54.683100',
            publicLongitude: '25.289700',
            exactLatitude: '54.683100',
            exactLongitude: '25.289700',
            locale: 'lt',
            idempotencyKey: 'demo-place-welfare-community-centre-0001',
            summary: 'A verified organization venue for education and community coordination.',
            petRules: 'Animals use the designated entrance and quiet rest area.',
            isIndoor: true,
            verificationStatus: PlaceVerificationStatus::OrganizationConfirmed,
            accessibilityStatus: PlaceAccessibilityStatus::Confirmed,
            organizationId: $organization->id,
        ));
        $place->forceFill([
            'stable_key' => 'demo-place-welfare-community-centre',
            'slug' => 'vilnius-welfare-community-centre',
            'verification_source' => 'demo-organization-facility-register',
            'verified_at' => now(),
            'information_expires_at' => now()->addYear(),
            'accessibility_facts' => ['step_free_entrance', 'accessible_toilet', 'hearing_loop'],
        ])->save();

        return $place;
    }

    private function privateFosterPlace(User $actor, Organization $organization): Place
    {
        $place = app(CreatePlace::class)->handle($actor, new CreatePlaceData(
            name: 'Protected foster introduction space',
            type: PlaceType::FosterLocation,
            visibility: PlaceVisibility::Private,
            publicRegion: 'Vilnius North',
            publicAddress: null,
            exactAddress: 'Protected foster entrance, Building 4',
            publicLatitude: '54.730000',
            publicLongitude: '25.280000',
            exactLatitude: '54.731234',
            exactLongitude: '25.281234',
            locale: 'en',
            idempotencyKey: 'demo-place-protected-foster-0001',
            summary: 'A controlled introduction location available only to approved participants.',
            privateInstructions: 'Use the staffed side entrance at the approved access time.',
            petRules: 'No unscheduled arrivals or public spectators.',
            isIndoor: true,
            verificationStatus: PlaceVerificationStatus::OrganizationConfirmed,
            accessibilityStatus: PlaceAccessibilityStatus::AccommodationOnRequest,
            organizationId: $organization->id,
        ));
        $place->forceFill([
            'stable_key' => 'demo-place-protected-foster',
            'slug' => 'protected-foster-introduction-space',
            'verification_source' => 'demo-private-foster-register',
            'verified_at' => now(),
            'information_expires_at' => now()->addMonths(6),
        ])->save();

        return $place;
    }

    private function venue(
        Place $place,
        ?Organization $organization,
        int $humanCapacity,
        int $animalCapacity,
    ): Venue {
        return Venue::query()->updateOrCreate(
            ['place_id' => $place->id],
            [
                'organization_id' => $organization?->id,
                'status' => VenueStatus::Active,
                'timezone' => 'Europe/Vilnius',
                'human_capacity' => $humanCapacity,
                'animal_capacity' => $animalCapacity,
                'species_capacities' => ['dog' => $animalCapacity],
                'staff_to_participant_ratio' => 10,
                'operational_contact' => 'demo-venue-operations@example.test',
                'operational_rules' => ['Keep emergency exits and animal rest routes clear.'],
                'confirmed_at' => now(),
                'information_expires_at' => now()->addYear(),
            ],
        );
    }

    private function area(
        Venue $venue,
        string $stableKey,
        string $name,
        VenueAreaType $type,
        int $humanCapacity,
        int $animalCapacity,
        bool $isPublic,
    ): VenueArea {
        return VenueArea::query()->updateOrCreate(
            ['venue_id' => $venue->id, 'stable_key' => $stableKey],
            [
                'name' => $name,
                'type' => $type,
                'is_public' => $isPublic,
                'human_capacity' => $humanCapacity,
                'animal_capacity' => $animalCapacity,
                'species_capacities' => ['dog' => $animalCapacity],
                'accessibility_status' => PlaceAccessibilityStatus::Confirmed,
                'accessibility_facts' => ['step_free_access'],
                'private_instructions' => $isPublic ? null : 'Authorized staff access only.',
            ],
        );
    }

    private function linkEvent(
        string $stableKey,
        Place $place,
        ?Venue $venue,
        User $actor,
    ): ?ForumEvent {
        $event = ForumEvent::query()->where('stable_key', $stableKey)->first();

        if ($event === null) {
            return null;
        }

        return DB::transaction(function () use ($actor, $event, $place, $venue): ForumEvent {
            $locked = ForumEvent::query()->lockForUpdate()->findOrFail($event->id);
            $locked->forceFill([
                'place_id' => $place->id,
                'venue_id' => $venue?->id,
                'location_scope' => $place->public_region,
                'exact_location' => null,
            ])->save();
            $locked->occurrences()->update([
                'place_id' => $place->id,
                'venue_id' => $venue?->id,
                'location_scope' => $place->public_region,
                'exact_location' => null,
                'updated_at' => now(),
            ]);

            $existingVersion = $locked->versions()
                ->where('reason_code', 'canonical-place-linked')
                ->first();

            if ($existingVersion === null) {
                $versionNumber = ((int) $locked->versions()->max('version_number')) + 1;
                $locked->forceFill(['current_version_number' => $versionNumber])->save();
                $snapshot = app(ForumEventLifecycleSnapshot::class)->event($locked);
                ForumEventVersion::query()->create([
                    'forum_event_id' => $locked->id,
                    'version_number' => $versionNumber,
                    'created_by_user_id' => $actor->id,
                    'kind' => 'material_change',
                    'reason_code' => 'canonical-place-linked',
                    'snapshot' => $snapshot,
                    'snapshot_checksum' => app(ForumEventLifecycleSnapshot::class)->checksum($snapshot),
                    'material_fields' => ['place_id', 'venue_id', 'location_scope'],
                    'published_at' => $locked->published_at,
                    'created_at' => now(),
                ]);
            }

            return $locked->refresh();
        }, 3);
    }
}
