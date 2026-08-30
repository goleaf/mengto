<?php

declare(strict_types=1);

use App\Enums\PetSizeCategory;
use App\Enums\PlaceEligibilityState;
use App\Enums\PlaceServiceAccessMode;
use App\Enums\PlaceServiceAvailability;
use App\Enums\PlaceSupportScope;
use App\Models\Place;
use App\Models\PlaceFact;
use App\Models\PlaceFactSelection;
use App\Models\PlaceServiceCapability;
use App\Models\PlaceServiceDefinition;
use App\Models\PlaceServiceOffering;
use App\Models\Taxon;
use App\Services\PlaceEmergencyEligibility;

test('veterinary category alone never implies emergency capability', function (): void {
    $place = Place::factory()->create(['catalog_category' => 'emergency-vet']);
    $bird = Taxon::factory()->create(['stable_key' => 'taxon.species.bird']);

    $result = app(PlaceEmergencyEligibility::class)->evaluate(
        place: $place,
        taxon: $bird,
        size: PetSizeCategory::Small,
    );

    expect($result->service)->toBe(PlaceEligibilityState::Unknown)
        ->and($result->species)->toBe(PlaceEligibilityState::Unknown)
        ->and($result->eligible)->toBeFalse();
});

test('unavailable emergency offering is explicit and cannot qualify a place', function (): void {
    $place = Place::factory()->create();
    $bird = Taxon::factory()->create(['stable_key' => 'taxon.species.bird']);
    emergencyOfferingFor($place, PlaceServiceAvailability::Unavailable);

    $result = app(PlaceEmergencyEligibility::class)->evaluate(
        place: $place,
        taxon: $bird,
        size: PetSizeCategory::Small,
    );

    expect($result->service)->toBe(PlaceEligibilityState::Unavailable)
        ->and($result->eligible)->toBeFalse();
});

test('emergency eligibility requires explicit offering species and size support', function (): void {
    $place = Place::factory()->create();
    $bird = Taxon::factory()->create(['stable_key' => 'taxon.species.bird']);
    $dog = Taxon::factory()->create(['stable_key' => 'taxon.species.dog']);
    $offering = emergencyOfferingFor($place, PlaceServiceAvailability::Available, restricted: true);
    $offering->taxa()->attach($bird->id, [
        'eligibility' => PlaceEligibilityState::Supported->value,
        'includes_descendants' => false,
        'condition_code' => null,
    ]);
    $offering->taxa()->attach($dog->id, [
        'eligibility' => PlaceEligibilityState::NotSupported->value,
        'includes_descendants' => false,
        'condition_code' => null,
    ]);
    $offering->sizes()->attach(PetSizeCategory::Small->value, [
        'eligibility' => PlaceEligibilityState::Supported->value,
        'condition_code' => null,
    ]);
    $offering->sizes()->attach(PetSizeCategory::Large->value, [
        'eligibility' => PlaceEligibilityState::NotSupported->value,
        'condition_code' => null,
    ]);

    $eligible = app(PlaceEmergencyEligibility::class)->evaluate(
        place: $place,
        taxon: $bird,
        size: PetSizeCategory::Small,
    );
    $wrongSpecies = app(PlaceEmergencyEligibility::class)->evaluate(
        place: $place,
        taxon: $dog,
        size: PetSizeCategory::Small,
    );
    $wrongSize = app(PlaceEmergencyEligibility::class)->evaluate(
        place: $place,
        taxon: $bird,
        size: PetSizeCategory::Large,
    );

    expect($eligible->service)->toBe(PlaceEligibilityState::Available)
        ->and($eligible->species)->toBe(PlaceEligibilityState::Supported)
        ->and($eligible->size)->toBe(PlaceEligibilityState::Supported)
        ->and($eligible->eligible)->toBeTrue()
        ->and($wrongSpecies->species)->toBe(PlaceEligibilityState::NotSupported)
        ->and($wrongSpecies->eligible)->toBeFalse()
        ->and($wrongSize->size)->toBe(PlaceEligibilityState::NotSupported)
        ->and($wrongSize->eligible)->toBeFalse();
});

test('missing species and size rows remain unknown rather than silently supported', function (): void {
    $place = Place::factory()->create();
    $bird = Taxon::factory()->create(['stable_key' => 'taxon.species.bird']);
    emergencyOfferingFor($place, PlaceServiceAvailability::Available, restricted: true);

    $result = app(PlaceEmergencyEligibility::class)->evaluate(
        place: $place,
        taxon: $bird,
        size: PetSizeCategory::Small,
    );

    expect($result->service)->toBe(PlaceEligibilityState::Available)
        ->and($result->species)->toBe(PlaceEligibilityState::Unknown)
        ->and($result->size)->toBe(PlaceEligibilityState::Unknown)
        ->and($result->eligible)->toBeFalse();
});

function emergencyOfferingFor(
    Place $place,
    PlaceServiceAvailability $availability,
    bool $restricted = false,
): PlaceServiceOffering {
    $capability = PlaceServiceCapability::query()->firstOrCreate(
        ['stable_key' => PlaceEmergencyEligibility::EMERGENCY_CAPABILITY_KEY],
        [
            'name_translation_key' => 'place_services.capabilities.veterinary_emergency_intake',
            'position' => 10,
            'is_active' => true,
        ],
    );
    $definition = PlaceServiceDefinition::query()->firstOrCreate(
        ['stable_key' => 'place-service.veterinary.emergency-triage'],
        [
            'name_translation_key' => 'place_services.services.veterinary_emergency_triage.name',
            'description_translation_key' => 'place_services.services.veterinary_emergency_triage.description',
            'service_domain' => 'veterinary',
            'position' => 10,
            'is_active' => true,
        ],
    );
    $definition->capabilities()->syncWithoutDetaching([$capability->id]);
    $fact = serviceCanonicalPlaceFact($place, 'service:emergency-triage');
    $offering = PlaceServiceOffering::factory()
        ->for($place)
        ->for($definition, 'definition')
        ->for($fact, 'fact')
        ->create([
            'availability' => $availability,
            'access_mode' => PlaceServiceAccessMode::CallRequired,
            'species_scope' => $restricted ? PlaceSupportScope::Restricted : PlaceSupportScope::Unknown,
            'size_scope' => $restricted ? PlaceSupportScope::Restricted : PlaceSupportScope::Unknown,
        ]);
    PlaceFactSelection::factory()->for($place)->for($fact, 'currentFact')->create([
        'field_slot' => 'service:emergency-triage',
    ]);

    return $offering;
}

function serviceCanonicalPlaceFact(Place $place, string $slot): PlaceFact
{
    return PlaceFact::factory()->canonicalFor($place, $slot)->create();
}
