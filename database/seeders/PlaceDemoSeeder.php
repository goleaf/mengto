<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlaceAccessibilityStatus;
use App\Enums\PlaceStatus;
use App\Enums\PlaceType;
use App\Enums\PlaceVerificationStatus;
use App\Enums\PlaceVisibility;
use App\Models\Place;
use App\Models\User;
use App\Services\PlaceCatalog;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use LogicException;

final class PlaceDemoSeeder extends Seeder
{
    public function run(PlaceCatalog $catalog): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Place catalog demo data requires an explicitly allowed environment.');
        }

        $demoOwner = User::query()
            ->where('actor_key', 'mia-carter')
            ->first();

        foreach ($catalog->demoRecords() as $record) {
            $place = Place::query()->firstOrNew([
                'stable_key' => $record['key'],
            ]);
            $isNew = ! $place->exists;
            $verifiedAt = CarbonImmutable::parse((string) $record['verification']['updated_at']);

            $place->forceFill([
                'owner_user_id' => $record['owner_managed'] ? $demoOwner?->id : null,
                'organization_id' => null,
                'created_by_user_id' => $demoOwner?->id,
                'last_edited_by_user_id' => $demoOwner?->id,
                'slug' => $record['key'],
                ...($isNew ? [
                    'creation_idempotency_key' => 'place-demo-'.$record['key'],
                ] : []),
                'name' => $record['name'],
                'summary' => $record['summary'],
                'type' => $this->placeType((string) $record['primary_category']),
                'catalog_category' => $record['primary_category'],
                'visibility' => PlaceVisibility::Public,
                'status' => PlaceStatus::Active,
                'locale' => app()->getLocale(),
                'public_region' => $record['city'],
                'public_address' => $record['address'],
                'public_phone' => $record['phone'],
                'public_website' => $record['website'],
                'public_email' => $record['email'],
                'public_latitude' => $record['latitude'],
                'public_longitude' => $record['longitude'],
                ...($isNew ? [
                    'exact_address' => null,
                    'exact_latitude' => null,
                    'exact_longitude' => null,
                    'private_instructions' => null,
                ] : []),
                'is_indoor' => in_array($record['primary_category'], [
                    'vet',
                    'emergency-vet',
                    'pet-store',
                    'grooming',
                    'shelter',
                    'pet-cafe',
                ], true),
                'verification_status' => $this->verificationStatus(
                    (string) $record['verification']['tone'],
                ),
                'verification_source' => $record['verification']['label'],
                'verified_at' => $verifiedAt,
                'information_expires_at' => $verifiedAt->addYear(),
                'accessibility_status' => $record['wheelchair_access']
                    ? PlaceAccessibilityStatus::Confirmed
                    : PlaceAccessibilityStatus::NotAssessed,
                'accessibility_facts' => array_values($record['accessibility']),
                'transport_information' => $record['general_location'],
                'parking_information' => $record['parking']
                    ? __('places.presentation.parking_listed')
                    : null,
                'pet_rules' => implode("\n", $record['rules']),
                'species_rules' => array_values($record['accepted_species']),
                'metadata' => ['demo_catalog' => true],
                'archived_at' => null,
            ])->save();
        }
    }

    private function placeType(string $category): PlaceType
    {
        return match ($category) {
            'park', 'dog-park' => PlaceType::Park,
            'route' => PlaceType::WalkingRoute,
            'vet', 'emergency-vet' => PlaceType::VeterinaryClinic,
            'shelter' => PlaceType::Shelter,
            default => PlaceType::PublicSpace,
        };
    }

    private function verificationStatus(string $tone): PlaceVerificationStatus
    {
        return match ($tone) {
            'verified' => PlaceVerificationStatus::Verified,
            'community' => PlaceVerificationStatus::OrganizationConfirmed,
            default => PlaceVerificationStatus::OrganizerProvided,
        };
    }
}
