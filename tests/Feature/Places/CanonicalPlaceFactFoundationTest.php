<?php

declare(strict_types=1);

use App\Actions\ReplacePlaceContactPoint;
use App\Data\Places\CanonicalPlaceFactData;
use App\Data\Places\ReplacePlaceContactPointData;
use App\Enums\PlaceContactPurpose;
use App\Enums\PlaceContactType;
use App\Enums\PlaceFactVisibility;
use App\Enums\PlaceSubmissionSource;
use App\Enums\PlaceVerificationScope;
use App\Models\Place;
use App\Models\PlaceCategory;
use App\Models\PlaceCategoryName;
use App\Models\PlaceContactPoint;
use App\Models\PlaceFact;
use App\Models\PlaceFactSelection;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

test('canonical place fact schema exposes normalized records and immutable provenance selectors', function (): void {
    expect(Schema::hasTable('place_fact_selections'))->toBeTrue()
        ->and(Schema::hasTable('place_fact_evidence_links'))->toBeTrue()
        ->and(Schema::hasTable('place_categories'))->toBeTrue()
        ->and(Schema::hasTable('place_category_names'))->toBeTrue()
        ->and(Schema::hasTable('place_category_assignments'))->toBeTrue()
        ->and(Schema::hasTable('place_contact_points'))->toBeTrue()
        ->and(Schema::hasTable('place_schedules'))->toBeTrue()
        ->and(Schema::hasTable('place_opening_intervals'))->toBeTrue()
        ->and(Schema::hasTable('place_schedule_exceptions'))->toBeTrue()
        ->and(Schema::hasTable('place_schedule_exception_intervals'))->toBeTrue()
        ->and(Schema::hasTable('place_temporary_closures'))->toBeTrue()
        ->and(Schema::hasTable('place_service_definitions'))->toBeTrue()
        ->and(Schema::hasTable('place_service_capabilities'))->toBeTrue()
        ->and(Schema::hasTable('place_service_definition_capability'))->toBeTrue()
        ->and(Schema::hasTable('place_service_offerings'))->toBeTrue()
        ->and(Schema::hasTable('place_service_offering_taxon'))->toBeTrue()
        ->and(Schema::hasTable('place_service_offering_size'))->toBeTrue()
        ->and(Schema::hasTable('place_taxon_supports'))->toBeTrue()
        ->and(Schema::hasTable('place_size_supports'))->toBeTrue()
        ->and(Schema::hasTable('place_attribute_definitions'))->toBeTrue()
        ->and(Schema::hasTable('place_attribute_values'))->toBeTrue()
        ->and(Schema::hasColumns('place_facts', [
            'verification_scope',
            'fresh_until',
            'expires_at',
            'supersedes_fact_id',
            'operation_key',
            'payload_fingerprint',
        ]))->toBeTrue();
});

test('category names are locale unique and category assignments cannot duplicate the same fact', function (): void {
    $category = PlaceCategory::factory()->create(['stable_key' => 'place-category.veterinary']);

    PlaceCategoryName::factory()->for($category, 'category')->create([
        'locale' => 'en',
        'name' => 'Veterinary clinic',
    ]);

    expect(fn () => PlaceCategoryName::factory()->for($category, 'category')->create([
        'locale' => 'en',
        'name' => 'Duplicate veterinary name',
    ]))->toThrow(QueryException::class);
});

test('a manager can append and replace a contact while evidence and the prior version remain immutable', function (): void {
    CarbonImmutable::setTestNow('2026-08-30 10:00:00 UTC');
    $manager = User::factory()->create();
    $place = Place::factory()->for($manager, 'owner')->create(['lock_version' => 0]);
    $action = app(ReplacePlaceContactPoint::class);

    $first = $action->handle(
        actor: $manager,
        place: $place,
        data: canonicalContactData(
            value: '+37060000111',
            idempotencyKey: 'contact-general-phone-create-0001',
            expectedPlaceVersion: 0,
            expectedSelectionVersion: null,
            sourceReference: 'Manager confirmation call log 001.',
        ),
    );
    $replayed = $action->handle(
        actor: $manager,
        place: $place->fresh(),
        data: canonicalContactData(
            value: '+37060000111',
            idempotencyKey: 'contact-general-phone-create-0001',
            expectedPlaceVersion: 0,
            expectedSelectionVersion: null,
            sourceReference: 'Manager confirmation call log 001.',
        ),
    );

    expect($replayed->is($first))->toBeTrue()
        ->and(PlaceContactPoint::query()->count())->toBe(1)
        ->and(PlaceFact::query()->where('place_id', $place->id)->count())->toBe(1)
        ->and($first->fact->verification_scope)->toBe(PlaceVerificationScope::ManagerConfirmed)
        ->and($first->fact->fresh_until?->toAtomString())->toBe('2026-09-29T10:00:00+00:00');

    $replacement = $action->handle(
        actor: $manager,
        place: $place->fresh(),
        data: canonicalContactData(
            value: '+37060000222',
            idempotencyKey: 'contact-general-phone-replace-0001',
            expectedPlaceVersion: 1,
            expectedSelectionVersion: 0,
            sourceReference: 'Corrected manager confirmation call log 002.',
            evidenceFactIds: [$first->place_fact_id],
        ),
    );

    $selection = PlaceFactSelection::query()
        ->where('place_id', $place->id)
        ->where('field_slot', 'contact:general-phone')
        ->sole();

    expect($replacement->value)->toBe('+37060000222')
        ->and($replacement->fact->supersedes_fact_id)->toBe($first->place_fact_id)
        ->and($replacement->fact->evidenceFacts)->toHaveCount(1)
        ->and($replacement->fact->evidenceFacts->sole()->is($first->fact))->toBeTrue()
        ->and($selection->current_fact_id)->toBe($replacement->place_fact_id)
        ->and($selection->lock_version)->toBe(1)
        ->and(PlaceContactPoint::query()->count())->toBe(2)
        ->and(PlaceFact::query()->where('place_id', $place->id)->count())->toBe(2)
        ->and($first->fresh()->value)->toBe('+37060000111');
});

test('private contact values and source evidence are encrypted and excluded from serialization', function (): void {
    $manager = User::factory()->create();
    $place = Place::factory()->for($manager, 'owner')->create();

    $contact = app(ReplacePlaceContactPoint::class)->handle(
        actor: $manager,
        place: $place,
        data: canonicalContactData(
            value: 'private-contact@pawcircle.example',
            idempotencyKey: 'private-contact-create-0001',
            expectedPlaceVersion: 0,
            expectedSelectionVersion: null,
            sourceReference: 'Private supporting record with reporter identity.',
            visibility: PlaceFactVisibility::Private,
            type: PlaceContactType::Email,
        ),
    );

    $rawContact = DB::table('place_contact_points')->where('id', $contact->id)->first();
    $rawFact = DB::table('place_facts')->where('id', $contact->place_fact_id)->first();
    $serialized = json_encode($contact->load('fact')->toArray(), JSON_THROW_ON_ERROR);

    expect((string) $rawContact->private_value)->not->toContain('private-contact@pawcircle.example')
        ->and((string) $rawFact->source_reference)->not->toContain('reporter identity')
        ->and($serialized)->not->toContain('private-contact@pawcircle.example')
        ->and($serialized)->not->toContain('reporter identity')
        ->and($contact->public_value)->toBeNull()
        ->and($contact->private_value)->toBe('private-contact@pawcircle.example');
});

test('contact mutation rejects unauthorized and stale concurrent replacements', function (): void {
    $manager = User::factory()->create();
    $stranger = User::factory()->create();
    $place = Place::factory()->for($manager, 'owner')->create(['lock_version' => 0]);
    $action = app(ReplacePlaceContactPoint::class);

    expect(fn () => $action->handle(
        actor: $stranger,
        place: $place,
        data: canonicalContactData(
            value: '+37060000333',
            idempotencyKey: 'unauthorized-contact-create-0001',
            expectedPlaceVersion: 0,
            expectedSelectionVersion: null,
        ),
    ))->toThrow(AuthorizationException::class);

    $first = $action->handle(
        actor: $manager,
        place: $place,
        data: canonicalContactData(
            value: '+37060000444',
            idempotencyKey: 'concurrent-contact-create-0001',
            expectedPlaceVersion: 0,
            expectedSelectionVersion: null,
        ),
    );

    $action->handle(
        actor: $manager,
        place: $place->fresh(),
        data: canonicalContactData(
            value: '+37060000555',
            idempotencyKey: 'concurrent-contact-replace-winner',
            expectedPlaceVersion: 1,
            expectedSelectionVersion: 0,
            evidenceFactIds: [$first->place_fact_id],
        ),
    );

    expect(fn () => $action->handle(
        actor: $manager,
        place: $place->fresh(),
        data: canonicalContactData(
            value: '+37060000666',
            idempotencyKey: 'concurrent-contact-replace-loser',
            expectedPlaceVersion: 1,
            expectedSelectionVersion: 0,
            evidenceFactIds: [$first->place_fact_id],
        ),
    ))->toThrow(ValidationException::class)
        ->and(PlaceContactPoint::query()->count())->toBe(2)
        ->and(PlaceFactSelection::query()->sole()->lock_version)->toBe(1);
});

/**
 * @param  list<int>  $evidenceFactIds
 */
function canonicalContactData(
    string $value,
    string $idempotencyKey,
    int $expectedPlaceVersion,
    ?int $expectedSelectionVersion,
    ?string $sourceReference = null,
    array $evidenceFactIds = [],
    PlaceFactVisibility $visibility = PlaceFactVisibility::Public,
    PlaceContactType $type = PlaceContactType::Phone,
): ReplacePlaceContactPointData {
    return new ReplacePlaceContactPointData(
        slot: 'general-phone',
        type: $type,
        purpose: PlaceContactPurpose::General,
        visibility: $visibility,
        value: $value,
        position: 10,
        expectedPlaceVersion: $expectedPlaceVersion,
        expectedSelectionVersion: $expectedSelectionVersion,
        idempotencyKey: $idempotencyKey,
        fact: new CanonicalPlaceFactData(
            sourceKind: PlaceSubmissionSource::Organization,
            verificationScope: PlaceVerificationScope::ManagerConfirmed,
            visibility: $visibility,
            sourceReference: $sourceReference,
            observedAt: CarbonImmutable::parse('2026-08-30 09:00:00 UTC'),
            verifiedAt: CarbonImmutable::parse('2026-08-30 10:00:00 UTC'),
            freshUntil: CarbonImmutable::parse('2026-09-29 10:00:00 UTC'),
            expiresAt: null,
            evidenceFactIds: $evidenceFactIds,
        ),
    );
}
