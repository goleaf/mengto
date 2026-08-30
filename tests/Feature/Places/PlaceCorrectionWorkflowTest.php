<?php

declare(strict_types=1);

use App\Actions\ReviewPlaceCorrection;
use App\Actions\SubmitPlaceCorrection;
use App\Enums\OrganizationRole;
use App\Enums\PlaceCorrectionField;
use App\Enums\PlaceCorrectionResolution;
use App\Enums\PlaceCorrectionSource;
use App\Enums\PlaceCorrectionStatus;
use App\Models\OrganizationMembership;
use App\Models\Place;
use App\Models\PlaceCorrection;
use App\Models\PlaceCorrectionEvent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

test('a correction captures the canonical original fact and immutable provenance', function (): void {
    $place = Place::factory()->public()->create([
        'summary' => 'The original community garden description.',
        'lock_version' => 7,
    ]);
    $submitter = User::factory()->create();

    $correction = app(SubmitPlaceCorrection::class)->handle(
        actor: $submitter,
        place: $place,
        field: PlaceCorrectionField::Summary,
        proposedValue: 'The garden has an accessible water point for pets.',
        explanation: 'I visited yesterday and the public description is outdated.',
        evidence: 'https://example.test/garden-notice',
        source: PlaceCorrectionSource::PersonalObservation,
        observedAt: now()->subDay(),
        idempotencyKey: (string) Str::uuid(),
    );

    expect($correction->place_id)->toBe($place->id)
        ->and($correction->submitter_user_id)->toBe($submitter->id)
        ->and($correction->correction_field)->toBe(PlaceCorrectionField::Summary)
        ->and($correction->original_value)->toBe('The original community garden description.')
        ->and($correction->original_version)->toBe(7)
        ->and($correction->moderation_status)->toBe(PlaceCorrectionStatus::Pending)
        ->and($correction->events()->sole()->event_type)->toBe('submitted');

    expect(fn () => $correction->events()->sole()->update(['event_type' => 'rewritten']))
        ->toThrow(LogicException::class);
});

test('correction submission is replay-safe but rejects an idempotency-key payload conflict', function (): void {
    $place = Place::factory()->public()->create();
    $submitter = User::factory()->create();
    $key = (string) Str::uuid();
    $action = app(SubmitPlaceCorrection::class);

    $first = $action->handle(
        $submitter,
        $place,
        PlaceCorrectionField::PetRules,
        'Dogs must remain on a short lead near the pond.',
        'The updated sign is fixed beside the pond entrance.',
        null,
        PlaceCorrectionSource::PublicSource,
        null,
        $key,
    );
    $replay = $action->handle(
        $submitter,
        $place,
        PlaceCorrectionField::PetRules,
        'Dogs must remain on a short lead near the pond.',
        'The updated sign is fixed beside the pond entrance.',
        null,
        PlaceCorrectionSource::PublicSource,
        null,
        $key,
    );

    expect($replay->id)->toBe($first->id)
        ->and(PlaceCorrection::query()->count())->toBe(1)
        ->and(PlaceCorrectionEvent::query()->count())->toBe(1);

    expect(fn () => $action->handle(
        $submitter,
        $place,
        PlaceCorrectionField::PetRules,
        'Dogs are prohibited near the pond.',
        'A conflicting replay must be rejected.',
        null,
        PlaceCorrectionSource::PublicSource,
        null,
        $key,
    ))->toThrow(ValidationException::class);
});

test('only the verified place manager or moderator may review a correction', function (): void {
    $manager = User::factory()->create();
    $place = Place::factory()->public()->for($manager, 'owner')->create();
    $correction = PlaceCorrection::factory()->for($place)->create();
    $outsider = User::factory()->create();

    expect(fn () => app(ReviewPlaceCorrection::class)->handle(
        $outsider,
        $correction,
        PlaceCorrectionStatus::Rejected,
        'This review must not be accepted from an unrelated account.',
        (string) Str::uuid(),
    ))->toThrow(AuthorizationException::class);

    $correction->refresh();

    expect($correction->moderation_status)->toBe(PlaceCorrectionStatus::Pending)
        ->and($correction->reviewer_user_id)->toBeNull();
});

test('an active organization place manager may review while an ordinary member may not', function (): void {
    $organizationManager = User::factory()->create();
    $ordinaryMember = User::factory()->create();
    $place = Place::factory()->public()->forOrganization()->create();
    OrganizationMembership::factory()->for($place->organization)->for($organizationManager)->create([
        'role' => OrganizationRole::SafetyLead,
    ]);
    OrganizationMembership::factory()->for($place->organization)->for($ordinaryMember)->create([
        'role' => OrganizationRole::Member,
    ]);
    $correction = PlaceCorrection::factory()->for($place)->create();

    expect(fn () => app(ReviewPlaceCorrection::class)->handle(
        $ordinaryMember,
        $correction,
        PlaceCorrectionStatus::Rejected,
        'An ordinary organization member cannot resolve corrections.',
        (string) Str::uuid(),
    ))->toThrow(AuthorizationException::class);

    $reviewed = app(ReviewPlaceCorrection::class)->handle(
        $organizationManager,
        $correction,
        PlaceCorrectionStatus::Rejected,
        'The correction evidence does not support this place fact.',
        (string) Str::uuid(),
    );

    expect($reviewed->reviewer_user_id)->toBe($organizationManager->id)
        ->and($reviewed->moderation_status)->toBe(PlaceCorrectionStatus::Rejected);
});

test('an accepted correction applies its field through a locked canonical place mutation', function (): void {
    $manager = User::factory()->create();
    $place = Place::factory()->public()->for($manager, 'owner')->create([
        'summary' => 'The old description.',
        'lock_version' => 4,
    ]);
    $correction = app(SubmitPlaceCorrection::class)->handle(
        User::factory()->create(),
        $place,
        PlaceCorrectionField::Summary,
        'The updated accessible description.',
        'The entrance and water point were checked this morning.',
        'https://example.test/accessible-description',
        PlaceCorrectionSource::OfficialSource,
        now(),
        (string) Str::uuid(),
    );

    $reviewed = app(ReviewPlaceCorrection::class)->handle(
        $manager,
        $correction,
        PlaceCorrectionStatus::Accepted,
        'The official notice confirms this correction.',
        (string) Str::uuid(),
    );

    $place->refresh();

    expect($reviewed->moderation_status)->toBe(PlaceCorrectionStatus::Accepted)
        ->and($reviewed->resolution)->toBe(PlaceCorrectionResolution::Applied)
        ->and($reviewed->reviewer_user_id)->toBe($manager->id)
        ->and($reviewed->applied_by_user_id)->toBe($manager->id)
        ->and($reviewed->applied_value)->toBe('The updated accessible description.')
        ->and($place->summary)->toBe('The updated accessible description.')
        ->and($place->last_edited_by_user_id)->toBe($manager->id)
        ->and($place->lock_version)->toBe(5)
        ->and($reviewed->events()->count())->toBe(2);
});

test('a correction decision is replay-safe and cannot be reused for another decision', function (): void {
    $manager = User::factory()->create();
    $place = Place::factory()->public()->for($manager, 'owner')->create();
    $correction = app(SubmitPlaceCorrection::class)->handle(
        User::factory()->create(),
        $place,
        PlaceCorrectionField::Summary,
        'A verified updated description.',
        'The public registry was updated after a site inspection.',
        null,
        PlaceCorrectionSource::OfficialSource,
        null,
        (string) Str::uuid(),
    );
    $key = (string) Str::uuid();
    $action = app(ReviewPlaceCorrection::class);

    $first = $action->handle(
        $manager,
        $correction,
        PlaceCorrectionStatus::Rejected,
        'The supplied registry reference does not identify this place.',
        $key,
    );
    $replay = $action->handle(
        $manager,
        $correction,
        PlaceCorrectionStatus::Rejected,
        'The supplied registry reference does not identify this place.',
        $key,
    );

    expect($replay->id)->toBe($first->id)
        ->and($replay->events()->count())->toBe(2);

    expect(fn () => $action->handle(
        $manager,
        $correction,
        PlaceCorrectionStatus::Superseded,
        'This idempotency key cannot be used for a different resolution.',
        $key,
    ))->toThrow(ValidationException::class);
});

test('a stale correction requires an explicit merge decision before it can be applied', function (): void {
    $manager = User::factory()->create();
    $place = Place::factory()->public()->for($manager, 'owner')->create([
        'summary' => 'Original description.',
        'lock_version' => 2,
    ]);
    $correction = app(SubmitPlaceCorrection::class)->handle(
        User::factory()->create(),
        $place,
        PlaceCorrectionField::Summary,
        'Corrected description.',
        'A current public notice supports this update.',
        null,
        PlaceCorrectionSource::PublicSource,
        null,
        (string) Str::uuid(),
    );
    $place->forceFill([
        'summary' => 'A newer manager edit.',
        'lock_version' => 3,
    ])->save();

    expect(fn () => app(ReviewPlaceCorrection::class)->handle(
        $manager,
        $correction,
        PlaceCorrectionStatus::Accepted,
        'The correction should not silently overwrite a newer fact.',
        (string) Str::uuid(),
    ))->toThrow(ValidationException::class);

    $merged = app(ReviewPlaceCorrection::class)->handle(
        $manager,
        $correction,
        PlaceCorrectionStatus::Accepted,
        'The reviewer explicitly merged the newer evidence.',
        (string) Str::uuid(),
        acceptStaleMerge: true,
    );

    expect($merged->resolution)->toBe(PlaceCorrectionResolution::Applied)
        ->and($merged->events()->latest('id')->firstOrFail()->event_type)->toBe('accepted_after_stale_merge');
});
