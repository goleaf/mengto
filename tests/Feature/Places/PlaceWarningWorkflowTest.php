<?php

declare(strict_types=1);

use App\Actions\AppealPlaceWarning;
use App\Actions\ConfirmPlaceWarning;
use App\Actions\DisputePlaceWarning;
use App\Actions\ExpirePlaceWarnings;
use App\Actions\ResolvePlaceWarning;
use App\Actions\SubmitPlaceWarning;
use App\Enums\PlaceWarningCategory;
use App\Enums\PlaceWarningResolution;
use App\Enums\PlaceWarningSeverity;
use App\Enums\PlaceWarningSource;
use App\Enums\PlaceWarningStatus;
use App\Models\Place;
use App\Models\PlaceWarning;
use App\Models\PlaceWarningConfirmation;
use App\Models\PlaceWarningEvent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

test('a verified member creates a bounded low severity warning which is published with immutable provenance', function (): void {
    $place = Place::factory()->public()->create();
    $actor = User::factory()->create();

    $warning = app(SubmitPlaceWarning::class)->handle(
        actor: $actor,
        place: $place,
        category: PlaceWarningCategory::Hazard,
        severity: PlaceWarningSeverity::Low,
        affectedScope: 'The public entrance ramp only.',
        source: PlaceWarningSource::PersonalObservation,
        title: 'Wet ramp surface after the rain',
        detail: 'The public ramp was slippery this morning. Please use care while entering.',
        evidence: 'Observed at 08:15 from the public pavement.',
        expiresAt: now()->addHours(8),
        idempotencyKey: (string) Str::uuid(),
    );

    expect($warning->status)->toBe(PlaceWarningStatus::Published)
        ->and($warning->published_at)->not->toBeNull()
        ->and($warning->author_user_id)->toBe($actor->id)
        ->and($warning->expires_at->isFuture())->toBeTrue()
        ->and(PlaceWarningEvent::query()->where('place_warning_id', $warning->id)->pluck('event_type')->all())
        ->toContain('submitted', 'published');
});

test('high severity warnings require moderation and submissions replay only their exact payload', function (): void {
    $place = Place::factory()->public()->create();
    $actor = User::factory()->create();
    $idempotencyKey = (string) Str::uuid();
    $expiry = now()->addDay();

    $first = app(SubmitPlaceWarning::class)->handle(
        $actor,
        $place,
        PlaceWarningCategory::AnimalHealth,
        PlaceWarningSeverity::High,
        'Public drinking-bowl area.',
        PlaceWarningSource::PersonalObservation,
        'Possible contaminated water bowl',
        'Several animals avoided the public bowl during a walk this afternoon.',
        'Photo submitted to moderators separately.',
        $expiry,
        $idempotencyKey,
    );
    $replay = app(SubmitPlaceWarning::class)->handle(
        $actor,
        $place,
        PlaceWarningCategory::AnimalHealth,
        PlaceWarningSeverity::High,
        'Public drinking-bowl area.',
        PlaceWarningSource::PersonalObservation,
        'Possible contaminated water bowl',
        'Several animals avoided the public bowl during a walk this afternoon.',
        'Photo submitted to moderators separately.',
        $expiry,
        $idempotencyKey,
    );

    expect($first->status)->toBe(PlaceWarningStatus::NeedsReview)
        ->and($replay->id)->toBe($first->id)
        ->and(PlaceWarning::query()->count())->toBe(1);

    expect(fn (): PlaceWarning => app(SubmitPlaceWarning::class)->handle(
        $actor,
        $place,
        PlaceWarningCategory::AnimalHealth,
        PlaceWarningSeverity::High,
        'A different scope must not replay.',
        PlaceWarningSource::PersonalObservation,
        'Possible contaminated water bowl',
        'Several animals avoided the public bowl during a walk this afternoon.',
        'Photo submitted to moderators separately.',
        $expiry,
        $idempotencyKey,
    ))->toThrow(ValidationException::class);
});

test('warning publication rejects markup and exact coordinate pairs from its public content', function (): void {
    $place = Place::factory()->public()->create();
    $actor = User::factory()->create();

    expect(fn () => app(SubmitPlaceWarning::class)->handle(
        $actor,
        $place,
        PlaceWarningCategory::Hazard,
        PlaceWarningSeverity::Low,
        'The public entrance at 54.687200, 25.279700.',
        PlaceWarningSource::PersonalObservation,
        'Temporary surface issue',
        'The public entrance is slippery after rain and requires extra care.',
        null,
        now()->addHour(),
        (string) Str::uuid(),
    ))->toThrow(ValidationException::class);

    expect(PlaceWarning::query()->count())->toBe(0);
});

test('confirmation is one per actor and an idempotent replay preserves the original timestamp', function (): void {
    $warning = PlaceWarning::factory()->published()->create();
    $actor = User::factory()->create();
    $key = (string) Str::uuid();

    $first = app(ConfirmPlaceWarning::class)->handle($actor, $warning, $key);
    Carbon::setTestNow(now()->addMinute());
    $replay = app(ConfirmPlaceWarning::class)->handle($actor, $warning, $key);
    Carbon::setTestNow();

    expect($first->id)->toBe($replay->id)
        ->and($replay->confirmed_at->equalTo($first->confirmed_at))->toBeTrue()
        ->and(PlaceWarningConfirmation::query()->where('place_warning_id', $warning->id)->count())->toBe(1);

    expect(fn () => app(ConfirmPlaceWarning::class)->handle($actor, $warning, (string) Str::uuid()))
        ->toThrow(ValidationException::class);
});

test('manager scope governs disputes and moderation resolution while preserving the warning event trail', function (): void {
    $manager = User::factory()->create();
    $place = Place::factory()->public()->for($manager, 'owner')->create();
    $warning = PlaceWarning::factory()->published()->for($place)->create();
    $outsider = User::factory()->create();

    expect(fn () => app(DisputePlaceWarning::class)->handle(
        $outsider,
        $warning,
        'This person is not in verified management scope.',
        null,
        (string) Str::uuid(),
    ))->toThrow(AuthorizationException::class);

    $dispute = app(DisputePlaceWarning::class)->handle(
        $manager,
        $warning,
        'The ramp was repaired before this warning was submitted.',
        'Maintenance log reference 2026-08-30.',
        (string) Str::uuid(),
    );

    expect($dispute->place_warning_id)->toBe($warning->id);
    $warning->refresh();
    expect($warning->status)->toBe(PlaceWarningStatus::Disputed);

    $resolved = app(ResolvePlaceWarning::class)->handle(
        $manager,
        $warning,
        PlaceWarningStatus::Resolved,
        PlaceWarningResolution::Corrected,
        'Verified repair completed.',
    );

    expect($resolved->status)->toBe(PlaceWarningStatus::Resolved)
        ->and($resolved->resolution)->toBe(PlaceWarningResolution::Corrected)
        ->and(PlaceWarningEvent::query()->where('place_warning_id', $warning->id)->pluck('event_type')->all())
        ->toContain('disputed', 'resolved');
});

test('the warning author may appeal a rejected warning and expiry changes only active records', function (): void {
    $author = User::factory()->create();
    $warning = PlaceWarning::factory()->needsReview()->for($author, 'author')->create();
    $administrator = User::factory()->administrator()->create();

    app(ResolvePlaceWarning::class)->handle(
        $administrator,
        $warning,
        PlaceWarningStatus::Rejected,
        PlaceWarningResolution::InsufficientEvidence,
        'The supplied evidence cannot be verified.',
    );
    $appeal = app(AppealPlaceWarning::class)->handle(
        $author,
        $warning->fresh(),
        'A verifiable public record has now been supplied.',
        'Reference number 22 from the local authority.',
        (string) Str::uuid(),
    );

    $expired = PlaceWarning::factory()->published()->create(['expires_at' => now()->subMinute()]);
    $resolved = PlaceWarning::factory()->resolved()->create(['expires_at' => now()->subMinute()]);
    $changed = app(ExpirePlaceWarnings::class)->handle(now());

    expect($appeal->place_warning_id)->toBe($warning->id)
        ->and($expired->fresh()->status)->toBe(PlaceWarningStatus::Expired)
        ->and($resolved->fresh()->status)->toBe(PlaceWarningStatus::Resolved)
        ->and($changed)->toBe(1)
        ->and(PlaceWarningEvent::query()->where('place_warning_id', $expired->id)->pluck('event_type')->all())
        ->toContain('expired');
});
