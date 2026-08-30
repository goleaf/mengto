<?php

declare(strict_types=1);

use App\Enums\ForumEventSessionStatus;
use App\Enums\ForumEventStatus;
use App\Enums\PetProfileStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventSession;
use App\Models\ForumExpertSession;
use App\Models\PetProfile;
use App\Models\PetProfileFact;
use App\Models\SearchCase;
use Carbon\CarbonImmutable;

test('live event and session factory states persist a coherent schedule around now', function (): void {
    $now = CarbonImmutable::parse('2026-08-30 12:00:00 UTC');
    $this->travelTo($now);

    $event = ForumEvent::factory()->live()->create();
    $session = ForumEventSession::factory()->live()->create();
    $session->loadMissing(['event', 'occurrence']);

    expect($event->status)->toBe(ForumEventStatus::Live)
        ->and($event->starts_at->lt($now))->toBeTrue()
        ->and($event->ends_at->gt($now))->toBeTrue()
        ->and($session->status)->toBe(ForumEventSessionStatus::Live)
        ->and($session->event->status)->toBe(ForumEventStatus::Live)
        ->and($session->event->starts_at->lt($now))->toBeTrue()
        ->and($session->event->ends_at->gt($now))->toBeTrue()
        ->and($session->occurrence->status)->toBe(ForumEventStatus::Live)
        ->and($session->occurrence->forum_event_id)->toBe($session->forum_event_id)
        ->and($session->occurrence->starts_at->equalTo($session->event->starts_at))->toBeTrue()
        ->and($session->occurrence->ends_at->equalTo($session->event->ends_at))->toBeTrue()
        ->and($session->occurrence->starts_at->lte($session->starts_at))->toBeTrue()
        ->and($session->starts_at->lt($now))->toBeTrue()
        ->and($session->ends_at->gt($now))->toBeTrue()
        ->and($session->ends_at->lte($session->occurrence->ends_at))->toBeTrue()
        ->and($session->timezone)->toBe($session->occurrence->timezone);
});

test('expert session factory keeps question and session windows chronological', function (): void {
    $session = ForumExpertSession::factory()->create();

    expect($session->question_opens_at->lt($session->question_closes_at))->toBeTrue()
        ->and($session->question_closes_at->lte($session->ends_at))->toBeTrue()
        ->and($session->question_opens_at->lte($session->starts_at))->toBeTrue()
        ->and($session->starts_at->lt($session->ends_at))->toBeTrue();
});

test('search case factory states retain active keys only for active owned-animal cases', function (
    string $state,
    bool $expectsActiveKey,
): void {
    $case = SearchCase::factory()->{$state}()->create();

    expect($case->active_key !== null)->toBe($expectsActiveKey);
})->with([
    'found' => ['found', false],
    'returned' => ['returned', false],
    'sighted' => ['sighted', false],
    'reunited' => ['reunited', false],
    'stolen' => ['stolen', true],
]);

test('microchip record factory state persists the complete canonical private payload', function (): void {
    $factory = PetProfileFact::factory();

    expect(method_exists($factory, 'microchipRecord'))->toBeTrue();

    /** @var PetProfileFact $fact */
    $fact = $factory->microchipRecord('981020001234567')->create();
    $expectedValue = [
        'status' => 'chipped',
        'identifier' => '981020001234567',
        'documents_state' => 'available',
    ];

    expect($fact->fact_key)->toBe('microchip-record')
        ->and($fact->value)->toBe($expectedValue)
        ->and($fact->normalized_value_hash)->toBe(hash(
            'sha256',
            json_encode($expectedValue, JSON_THROW_ON_ERROR),
        ))
        ->and($fact->profile->currentMicrochipRecord?->is($fact))->toBeTrue();
});

test('pet profile factory exposes a persisted coherent state for every supported status', function (
    PetProfileStatus $status,
    string $state,
): void {
    $now = CarbonImmutable::parse('2026-08-30 12:00:00 UTC');
    $this->travelTo($now);
    $factory = PetProfile::factory();

    expect(method_exists($factory, $state))->toBeTrue();

    /** @var PetProfile $profile */
    $profile = $factory->{$state}()->create();

    expect($profile->status)->toBe($status)
        ->and($profile->state_entered_at?->equalTo($now))->toBeTrue()
        ->and($profile->is_discoverable)->toBe($status->isPubliclyEligible());

    if ($status->isPubliclyEligible()) {
        expect($profile->published_at)->not->toBeNull();
    }

    match ($status) {
        PetProfileStatus::Draft,
        PetProfileStatus::IdentityUnverified => expect($profile->published_at)->toBeNull(),
        PetProfileStatus::Hidden => expect($profile->hidden_at?->equalTo($now))->toBeTrue(),
        PetProfileStatus::Archived => expect($profile->archived_at?->equalTo($now))->toBeTrue(),
        PetProfileStatus::Memorial => expect($profile->memorialized_at?->equalTo($now))->toBeTrue(),
        PetProfileStatus::Merged => expect($profile->merged_at?->equalTo($now))->toBeTrue()
            ->and($profile->canonicalProfile)->not->toBeNull()
            ->and($profile->canonical_profile_id)->not->toBe($profile->id),
        PetProfileStatus::DeletionPending => expect($profile->deletion_requested_at?->equalTo($now))->toBeTrue()
            ->and($profile->deletion_scheduled_for?->gt($now))->toBeTrue(),
        default => null,
    };
})->with([
    'draft' => [PetProfileStatus::Draft, 'draft'],
    'active' => [PetProfileStatus::Active, 'active'],
    'foster care' => [PetProfileStatus::FosterCare, 'fosterCare'],
    'shelter' => [PetProfileStatus::Shelter, 'shelter'],
    'seeking home' => [PetProfileStatus::SeekingHome, 'seekingHome'],
    'adoption in progress' => [PetProfileStatus::AdoptionInProgress, 'adoptionInProgress'],
    'transferred' => [PetProfileStatus::Transferred, 'transferred'],
    'lost' => [PetProfileStatus::Lost, 'lost'],
    'found' => [PetProfileStatus::Found, 'found'],
    'identity unverified' => [PetProfileStatus::IdentityUnverified, 'identityUnverified'],
    'disputed ownership' => [PetProfileStatus::DisputedOwnership, 'disputedOwnership'],
    'hidden' => [PetProfileStatus::Hidden, 'hidden'],
    'memorial' => [PetProfileStatus::Memorial, 'memorial'],
    'merged' => [PetProfileStatus::Merged, 'merged'],
    'deletion pending' => [PetProfileStatus::DeletionPending, 'deletionPending'],
    'archived' => [PetProfileStatus::Archived, 'archived'],
]);
