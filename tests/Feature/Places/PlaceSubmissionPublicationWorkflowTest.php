<?php

declare(strict_types=1);

use App\Actions\ApprovePlaceSubmission;
use App\Actions\ConfirmPlaceDuplicateCandidate;
use App\Actions\ContinueDistinctPlaceSubmission;
use App\Actions\LinkPlaceSubmission;
use App\Actions\MergePlaceDuplicate;
use App\Actions\PublishPlaceSubmission;
use App\Actions\RejectPlaceSubmission;
use App\Actions\ReopenPlaceSubmission;
use App\Actions\RequestPlaceSubmissionInformation;
use App\Actions\RespondToPlaceSubmissionInformation;
use App\Actions\RestoreMergedPlace;
use App\Actions\SubmitPlaceSubmission;
use App\Actions\WithdrawPlaceSubmission;
use App\Data\SubmitPlaceData;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\PlaceLocationPrecision;
use App\Enums\PlaceStatus;
use App\Enums\PlaceSubmissionResolution;
use App\Enums\PlaceSubmissionSource;
use App\Enums\PlaceSubmissionStatus;
use App\Enums\PlaceType;
use App\Enums\PlaceVisibility;
use App\Livewire\Places\CreatePlaceSubmission;
use App\Livewire\Places\PlaceModerationWorkspace;
use App\Livewire\Places\PlaceSubmissionStatusPage;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Place;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceFact;
use App\Models\PlaceMergeRedirect;
use App\Models\PlaceSubmission;
use App\Models\PlaceSubmissionEvent;
use App\Models\PlaceSubmissionRevision;
use App\Models\User;
use App\Notifications\PlaceSubmissionStatusChanged;
use App\Services\PlaceDuplicateDetector;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('place submission persistence protects review state provenance candidates and redirects', function () {
    expect(Schema::hasTable('place_submissions'))->toBeTrue()
        ->and(Schema::hasTable('place_facts'))->toBeTrue()
        ->and(Schema::hasTable('place_duplicate_candidates'))->toBeTrue()
        ->and(Schema::hasTable('place_submission_events'))->toBeTrue()
        ->and(Schema::hasTable('place_merge_redirects'))->toBeTrue()
        ->and(Schema::hasTable('notifications'))->toBeTrue();

    $submission = PlaceSubmission::factory()->for($this->authenticatedUser, 'submitter')->create([
        'status' => PlaceSubmissionStatus::Submitted,
        'exact_address' => 'Private entrance 12',
        'audit_context' => ['request_id' => 'request-safe-001'],
    ]);
    $fact = PlaceFact::factory()->for($submission, 'submission')->create();
    $revision = PlaceSubmissionRevision::factory()
        ->for($submission, 'submission')
        ->for($this->authenticatedUser, 'submitter')
        ->create();
    $candidate = PlaceDuplicateCandidate::factory()->for($submission, 'submission')->create();
    $event = PlaceSubmissionEvent::factory()->for($submission, 'submission')->create();
    $redirect = PlaceMergeRedirect::factory()->create();
    $raw = DB::table('place_submissions')->where('id', $submission->id)->firstOrFail();

    expect($submission->status)->toBe(PlaceSubmissionStatus::Submitted)
        ->and($submission->facts->first()?->is($fact))->toBeTrue()
        ->and($submission->duplicateCandidates->first()?->is($candidate))->toBeTrue()
        ->and($submission->events->first()?->is($event))->toBeTrue()
        ->and($redirect->sourcePlace)->not->toBeNull()
        ->and($redirect->destinationPlace)->not->toBeNull()
        ->and((string) $raw->exact_address)->not->toContain('Private entrance 12')
        ->and($submission->toArray())->not->toHaveKeys([
            'exact_address',
            'exact_latitude',
            'exact_longitude',
            'audit_context',
            'payload_fingerprint',
            'idempotency_key',
            'submitted_facts',
            'normalized_email',
        ])
        ->and($candidate->toArray())->not->toHaveKeys([
            'candidate_place_id',
            'candidate_submission_id',
            'matched_signals',
            'signals_fingerprint',
        ]);

    expect(fn () => $fact->update(['field_key' => 'changed']))->toThrow(LogicException::class)
        ->and(fn () => $event->delete())->toThrow(LogicException::class)
        ->and(fn () => $revision->update(['kind' => 'changed']))->toThrow(LogicException::class)
        ->and(fn () => $candidate->delete())->toThrow(LogicException::class);
});

test('redirect history prevents a destructive rollback to globally unique source identifiers', function () {
    $identifier = 'remerged-place-history';
    PlaceMergeRedirect::factory()->create([
        'source_identifier' => $identifier,
        'active_source_identifier' => null,
        'restored_at' => now(),
    ]);
    PlaceMergeRedirect::factory()->create([
        'source_identifier' => $identifier,
        'active_source_identifier' => $identifier,
    ]);
    $migration = require database_path(
        'migrations/2026_08_30_121000_add_active_identifier_to_place_merge_redirects.php',
    );

    expect(fn () => $migration->down())->toThrow(
        LogicException::class,
        'Preserve their audit history and recover with a forward fix.',
    );

    expect(Schema::hasColumn('place_merge_redirects', 'active_source_identifier'))->toBeTrue()
        ->and(PlaceMergeRedirect::query()->where('source_identifier', $identifier)->count())->toBe(2);
});

test('an active verified member submits one pending review with facts audit and after-success notification', function () {
    Notification::fake();
    $action = app(SubmitPlaceSubmission::class);
    $data = placeSubmissionData('93a72f8d-9a35-4f8e-8596-08641465f311');

    $submission = $action->handle($this->authenticatedUser, $data);
    $same = $action->handle($this->authenticatedUser, $data);

    expect($same->is($submission))->toBeTrue()
        ->and($submission->status)->toBe(PlaceSubmissionStatus::Submitted)
        ->and($submission->facts()->count())->toBeGreaterThanOrEqual(10)
        ->and($submission->events()->count())->toBe(1)
        ->and(PlaceSubmission::query()->count())->toBe(1)
        ->and(Place::query()->count())->toBe(0);
    expect($submission->audit_context)->not->toHaveKey('request_id');
    Notification::assertSentTo(
        $this->authenticatedUser,
        PlaceSubmissionStatusChanged::class,
        fn (PlaceSubmissionStatusChanged $notification): bool => $notification->submissionId === $submission->id
            && $notification->status === PlaceSubmissionStatus::Submitted,
    );

    expect(fn () => $action->handle(
        $this->authenticatedUser,
        placeSubmissionData($data->idempotencyKey, name: 'A different place'),
    ))->toThrow(ValidationException::class);
    expect(PlaceSubmission::query()->count())->toBe(1)
        ->and(PlaceSubmissionEvent::query()->count())->toBe(1);
});

test('database notification payload is localized by key and excludes private workflow evidence', function () {
    $submission = app(SubmitPlaceSubmission::class)->handle(
        $this->authenticatedUser,
        placeSubmissionData('30000000-0000-4000-8000-000000000002', name: 'Safe Notification Place'),
    );
    $notification = DB::table('notifications')
        ->where('notifiable_type', $this->authenticatedUser->getMorphClass())
        ->where('notifiable_id', $this->authenticatedUser->id)
        ->sole();
    $payload = json_decode((string) $notification->data, true, flags: JSON_THROW_ON_ERROR);

    expect($payload)->toBe([
        'submission_key' => $submission->stable_key,
        'status' => $submission->status->value,
        'message_key' => 'places.submissions.notifications.'.$submission->status->value,
    ]);
});

test('submission rate limits count new work but an idempotent replay remains free', function () {
    Notification::fake();
    $shortKey = 'place-submission:10m:'.hash('sha256', (string) $this->authenticatedUser->id);
    $dailyKey = 'place-submission:day:'.hash('sha256', (string) $this->authenticatedUser->id);
    RateLimiter::clear($shortKey);
    RateLimiter::clear($dailyKey);

    $firstData = placeSubmissionData('30000000-0000-4000-8000-000000000010', name: 'Rate Place One');
    $first = app(SubmitPlaceSubmission::class)->handle($this->authenticatedUser, $firstData);
    expect(app(SubmitPlaceSubmission::class)->handle($this->authenticatedUser, $firstData)->is($first))->toBeTrue();

    app(SubmitPlaceSubmission::class)->handle(
        $this->authenticatedUser,
        placeSubmissionData('30000000-0000-4000-8000-000000000011', name: 'Rate Place Two'),
    );
    app(SubmitPlaceSubmission::class)->handle(
        $this->authenticatedUser,
        placeSubmissionData('30000000-0000-4000-8000-000000000012', name: 'Rate Place Three'),
    );

    expect(fn () => app(SubmitPlaceSubmission::class)->handle(
        $this->authenticatedUser,
        placeSubmissionData('30000000-0000-4000-8000-000000000013', name: 'Rate Place Four'),
    ))->toThrow(ValidationException::class)
        ->and(PlaceSubmission::query()->count())->toBe(3);
});

test('an outer transaction rollback discards the aggregate audit and deferred notification', function () {
    Notification::fake();

    expect(fn () => DB::transaction(function (): void {
        app(SubmitPlaceSubmission::class)->handle(
            $this->authenticatedUser,
            placeSubmissionData('30000000-0000-4000-8000-000000000001', name: 'Rolled Back Place'),
        );

        throw new RuntimeException('force outer rollback');
    }))->toThrow(RuntimeException::class);

    expect(PlaceSubmission::query()->count())->toBe(0)
        ->and(PlaceSubmissionEvent::query()->count())->toBe(0)
        ->and(PlaceFact::query()->count())->toBe(0);
    Notification::assertNothingSent();
});

test('deterministic duplicate signals create review candidates without creating or merging a place', function () {
    $existing = Place::factory()->public()->create([
        'name' => 'Žvėryno Pet Clinic',
        'normalized_name' => 'zveryno pet clinic',
        'catalog_category' => 'vet',
        'public_address' => 'Gedimino pr. 12, Vilnius',
        'normalized_address' => 'gedimino pr 12 vilnius',
        'public_phone' => '+370 612 34567',
        'normalized_phone' => '37061234567',
        'public_website' => 'https://example.lt/places',
        'normalized_website' => 'example.lt/places',
        'public_latitude' => '54.687200',
        'public_longitude' => '25.279700',
    ]);

    $submission = app(SubmitPlaceSubmission::class)->handle(
        $this->authenticatedUser,
        placeSubmissionData(
            'ba5041ce-c948-4bb7-a253-b07c6e8cf48d',
            name: '  ZVERYNO pet-clinic! ',
        ),
    );
    $candidate = $submission->duplicateCandidates()->sole();

    expect($submission->status)->toBe(PlaceSubmissionStatus::DuplicateReview)
        ->and($candidate->candidate_place_id)->toBe($existing->id)
        ->and($candidate->score)->toBeGreaterThanOrEqual(80)
        ->and($candidate->matched_signals)->toContain('name', 'address', 'phone', 'website', 'coordinates', 'category')
        ->and($candidate->presentation_scope)->toBe('member_visible')
        ->and(Place::query()->count())->toBe(1)
        ->and($existing->fresh()->status->value)->toBe('active');
});

test('duplicate scoring does not hide a strong candidate behind the bounded name cohort', function () {
    $submission = PlaceSubmission::factory()->for($this->authenticatedUser, 'submitter')->create([
        'normalized_name' => 'shared place name',
        'normalized_address' => '42 strong signal street',
        'normalized_phone' => '37061234999',
        'public_latitude' => null,
        'public_longitude' => null,
        'canonical_organization_id' => null,
    ]);

    Place::factory()->count(200)->create([
        'normalized_name' => 'shared place name',
        'normalized_address' => null,
        'normalized_phone' => null,
        'normalized_email' => null,
        'normalized_website' => null,
        'public_latitude' => null,
        'public_longitude' => null,
        'organization_id' => null,
    ]);
    $strongCandidate = Place::factory()->create([
        'normalized_name' => 'shared place name',
        'normalized_address' => '42 strong signal street',
        'normalized_phone' => '37061234999',
        'public_latitude' => null,
        'public_longitude' => null,
        'organization_id' => null,
    ]);

    app(PlaceDuplicateDetector::class)->detect($submission);

    expect($submission->duplicateCandidates()
        ->where('candidate_place_id', $strongCandidate->id)
        ->exists())->toBeTrue();
});

test('active merge aliases resolve duplicate suggestions to the canonical public place', function () {
    $destination = Place::factory()->public()->create([
        'name' => 'Canonical Community Clinic',
        'normalized_name' => 'canonical community clinic',
    ]);
    $source = Place::factory()->public()->create([
        'name' => 'Žvėryno Pet Clinic',
        'normalized_name' => 'zveryno pet clinic',
        'catalog_category' => 'vet',
        'public_address' => 'Gedimino pr. 12, Vilnius',
        'normalized_address' => 'gedimino pr 12 vilnius',
        'public_phone' => '+370 612 34567',
        'normalized_phone' => '37061234567',
        'public_website' => 'https://example.lt/places',
        'normalized_website' => 'example.lt/places',
        'public_latitude' => '54.687200',
        'public_longitude' => '25.279700',
        'status' => PlaceStatus::Merged,
        'merged_into_place_id' => $destination->id,
    ]);
    PlaceMergeRedirect::factory()->create([
        'source_place_id' => $source->id,
        'destination_place_id' => $destination->id,
        'source_identifier' => $source->slug,
        'source_visibility' => $source->visibility,
        'restored_at' => null,
    ]);

    $submission = app(SubmitPlaceSubmission::class)->handle(
        $this->authenticatedUser,
        placeSubmissionData(
            '526e92f8-da30-48b5-a435-eb334bbb061e',
            name: 'ZVERYNO pet-clinic',
        ),
    );
    $candidate = $submission->duplicateCandidates()->sole();

    expect($candidate->candidate_place_id)->toBe($destination->id)
        ->and($candidate->matched_signals)->toContain('alias')
        ->and($candidate->presentation_scope)->toBe('member_visible')
        ->and($source->fresh()->status)->toBe(PlaceStatus::Merged)
        ->and($destination->fresh()->status)->toBe(PlaceStatus::Active);
});

test('only an independent active moderator can approve and publish a visitor submission with provenance', function () {
    Notification::fake();
    $moderator = User::factory()->administrator()->create();
    $submission = app(SubmitPlaceSubmission::class)->handle(
        $this->authenticatedUser,
        placeSubmissionData('a6d71928-1797-4bd1-9746-439a9c35032e', name: 'Naujamiesčio Dog Park'),
    );

    expect(fn () => app(ApprovePlaceSubmission::class)->handle(
        $this->authenticatedUser,
        $submission,
        'd44b5791-542f-4f57-80b8-f824460ab99d',
        0,
        'confirmed-community-place',
    ))->toThrow(AuthorizationException::class);

    $approved = app(ApprovePlaceSubmission::class)->handle(
        $moderator,
        $submission,
        'd44b5791-542f-4f57-80b8-f824460ab99d',
        0,
        'confirmed-community-place',
    );
    $published = app(PublishPlaceSubmission::class)->handle(
        $moderator,
        $approved,
        '0e06e753-b19b-4e80-aa62-d4d2c74d7f25',
        1,
    );
    $replayed = app(PublishPlaceSubmission::class)->handle(
        $moderator,
        $published,
        '0e06e753-b19b-4e80-aa62-d4d2c74d7f25',
        1,
    );
    $place = $published->publishedPlace()->sole();

    expect($published->status)->toBe(PlaceSubmissionStatus::Published)
        ->and($published->resolution)->toBe(PlaceSubmissionResolution::NewPlace)
        ->and($place->owner_user_id)->toBeNull()
        ->and($place->created_by_user_id)->toBe($this->authenticatedUser->id)
        ->and($place->last_edited_by_user_id)->toBe($moderator->id)
        ->and($place->facts()->count())->toBe($submission->facts()->count())
        ->and($place->facts()->whereNotNull('copied_from_fact_id')->count())->toBe($submission->facts()->count())
        ->and($replayed->is($published))->toBeTrue()
        ->and($published->events()->count())->toBe(3);

    Notification::assertSentToTimes($this->authenticatedUser, PlaceSubmissionStatusChanged::class, 3);
    foreach ([
        PlaceSubmissionStatus::Submitted,
        PlaceSubmissionStatus::Approved,
        PlaceSubmissionStatus::Published,
    ] as $status) {
        Notification::assertSentTo(
            $this->authenticatedUser,
            PlaceSubmissionStatusChanged::class,
            static fn (PlaceSubmissionStatusChanged $notification): bool => $notification->status === $status,
        );
    }
});

test('moderation can link without creating or merging a canonical place and replay stays idempotent', function () {
    Notification::fake();
    $moderator = User::factory()->administrator()->create();
    $destination = Place::factory()->public()->create();
    $submission = PlaceSubmission::factory()->for($this->authenticatedUser, 'submitter')->duplicateReview()->create();
    $candidate = PlaceDuplicateCandidate::factory()
        ->for($submission, 'submission')
        ->for($destination, 'candidatePlace')
        ->create();
    $before = Place::query()->count();

    $linked = app(LinkPlaceSubmission::class)->handle(
        $moderator,
        $submission,
        $candidate,
        '56016e88-c77d-4e5a-bd5b-a44d03a65c61',
        0,
        'same-public-place',
    );
    $same = app(LinkPlaceSubmission::class)->handle(
        $moderator,
        $linked,
        $candidate,
        '56016e88-c77d-4e5a-bd5b-a44d03a65c61',
        0,
        'same-public-place',
    );

    expect($linked->status)->toBe(PlaceSubmissionStatus::Published)
        ->and($linked->resolution)->toBe(PlaceSubmissionResolution::ExistingLink)
        ->and($linked->linked_place_id)->toBe($destination->id)
        ->and(Place::query()->count())->toBe($before)
        ->and(PlaceMergeRedirect::query()->count())->toBe(0)
        ->and($same->is($linked))->toBeTrue();
});

test('moderation cannot link a submission to an archived candidate place', function () {
    $moderator = User::factory()->administrator()->create();
    $destination = Place::factory()->public()->create(['archived_at' => now()]);
    $submission = PlaceSubmission::factory()->for($this->authenticatedUser, 'submitter')->duplicateReview()->create();
    $candidate = PlaceDuplicateCandidate::factory()
        ->for($submission, 'submission')
        ->for($destination, 'candidatePlace')
        ->create();

    expect(fn () => app(LinkPlaceSubmission::class)->handle(
        $moderator,
        $submission,
        $candidate,
        'ae3ba76d-0f97-439f-8d84-058570bb8223',
        0,
        'same-archived-place',
    ))->toThrow(ValidationException::class);

    expect($submission->fresh()->status)->toBe(PlaceSubmissionStatus::DuplicateReview)
        ->and($submission->events()->count())->toBe(0);
});

test('a protected linked destination never exposes its identifier to the submitter', function () {
    $moderator = User::factory()->administrator()->create();
    $destination = Place::factory()->private()->create();
    $submission = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->duplicateReview()
        ->create();
    $candidate = PlaceDuplicateCandidate::factory()
        ->for($submission, 'submission')
        ->for($destination, 'candidatePlace')
        ->create(['presentation_scope' => 'review_only']);
    $linked = app(LinkPlaceSubmission::class)->handle(
        $moderator,
        $submission,
        $candidate,
        '973053c1-5c24-4dd0-a622-64c64de907b7',
        0,
        'same-protected-place',
    );

    expect($this->authenticatedUser->can('view', $destination))->toBeFalse();

    Livewire::actingAs($this->authenticatedUser)
        ->test(PlaceSubmissionStatusPage::class, ['placeSubmission' => $linked->stable_key])
        ->assertDontSee($destination->slug)
        ->assertDontSee($destination->name);
});

test('the submitter controls duplicate choices while unrelated members cannot act', function () {
    Notification::fake();
    $place = Place::factory()->public()->create();
    $submission = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->duplicateReview()
        ->create();
    $candidate = PlaceDuplicateCandidate::factory()
        ->for($submission, 'submission')
        ->for($place, 'candidatePlace')
        ->create(['presentation_scope' => 'member_visible']);
    $unrelated = User::factory()->create();

    expect(fn () => app(ConfirmPlaceDuplicateCandidate::class)->handle(
        $unrelated,
        $submission,
        $candidate,
        '83e40cac-cead-44cf-8ed0-8e8b686f9e09',
        0,
    ))->toThrow(AuthorizationException::class);

    $confirmed = app(ConfirmPlaceDuplicateCandidate::class)->handle(
        $this->authenticatedUser,
        $submission,
        $candidate,
        '83e40cac-cead-44cf-8ed0-8e8b686f9e09',
        0,
    );
    $replayed = app(ConfirmPlaceDuplicateCandidate::class)->handle(
        $this->authenticatedUser,
        $confirmed,
        $candidate,
        '83e40cac-cead-44cf-8ed0-8e8b686f9e09',
        0,
    );

    expect($confirmed->status)->toBe(PlaceSubmissionStatus::DuplicateReview)
        ->and($confirmed->reviewed_by_user_id)->toBeNull()
        ->and($confirmed->events()->latest('id')->firstOrFail()->place_duplicate_candidate_id)->toBe($candidate->id)
        ->and($replayed->is($confirmed))->toBeTrue();

    $distinctSubmission = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->duplicateReview()
        ->create();
    PlaceDuplicateCandidate::factory()
        ->for($distinctSubmission, 'submission')
        ->for($place, 'candidatePlace')
        ->create(['presentation_scope' => 'member_visible']);
    $continued = app(ContinueDistinctPlaceSubmission::class)->handle(
        $this->authenticatedUser,
        $distinctSubmission,
        '6e7e808d-263d-49ce-be03-1fc14b01bb08',
        0,
    );

    expect($continued->status)->toBe(PlaceSubmissionStatus::Submitted)
        ->and($continued->continued_as_distinct)->toBeTrue()
        ->and($continued->reviewed_by_user_id)->toBeNull();
});

test('the submitter can answer an information request and withdraw without losing history', function () {
    Notification::fake();
    $moderator = User::factory()->administrator()->create();
    $submission = PlaceSubmission::factory()->for($this->authenticatedUser, 'submitter')->create();
    $requested = app(RequestPlaceSubmissionInformation::class)->handle(
        $moderator,
        $submission,
        '91ae221e-87d8-4116-9a86-1b598e21cf6a',
        0,
        'source-needed',
        'Please provide the official source.',
    );

    Livewire::actingAs($this->authenticatedUser)
        ->test(PlaceSubmissionStatusPage::class, ['placeSubmission' => $requested->stable_key])
        ->assertSee('Please provide the official source.');

    $answered = app(RespondToPlaceSubmissionInformation::class)->handle(
        $this->authenticatedUser,
        $requested,
        'c1682ab2-fbf6-4e39-a4fe-e5ea2146c1c7',
        1,
        'The public hours are confirmed by the posted municipal notice dated today.',
    );
    $answerReplay = app(RespondToPlaceSubmissionInformation::class)->handle(
        $this->authenticatedUser,
        $answered,
        'c1682ab2-fbf6-4e39-a4fe-e5ea2146c1c7',
        1,
        'The public hours are confirmed by the posted municipal notice dated today.',
    );
    $withdrawn = app(WithdrawPlaceSubmission::class)->handle(
        $this->authenticatedUser,
        $answered,
        'b9cd38c9-26cb-4fd7-b7b5-b2ad03cba11d',
        2,
    );
    $withdrawReplay = app(WithdrawPlaceSubmission::class)->handle(
        $this->authenticatedUser,
        $withdrawn,
        'b9cd38c9-26cb-4fd7-b7b5-b2ad03cba11d',
        2,
    );

    expect($answered->status)->toBe(PlaceSubmissionStatus::Submitted)
        ->and($answerReplay->is($answered))->toBeTrue()
        ->and($withdrawReplay->is($withdrawn))->toBeTrue()
        ->and($answered->revisions()->where('kind', 'information-response')->count())->toBe(1)
        ->and($withdrawn->status)->toBe(PlaceSubmissionStatus::Withdrawn)
        ->and($withdrawn->withdrawn_at)->not->toBeNull()
        ->and($withdrawn->revisions()->count())->toBe(1)
        ->and($withdrawn->events()->pluck('action')->map->value->all())->toBe([
            'information-requested',
            'information-provided',
            'withdrawn',
        ]);

    Notification::assertSentToTimes($this->authenticatedUser, PlaceSubmissionStatusChanged::class, 3);
});

test('rejection information request and reopen are audited authorized and version locked', function () {
    Notification::fake();
    $moderator = User::factory()->administrator()->create();
    $submission = PlaceSubmission::factory()->for($this->authenticatedUser, 'submitter')->create();

    expect(fn () => app(RequestPlaceSubmissionInformation::class)->handle(
        $moderator,
        $submission,
        '5221529c-fb5c-4a46-965d-48b9a0f035c7',
        0,
        'contact-evidence-needed',
        '',
    ))->toThrow(ValidationException::class);

    $needsInformation = app(RequestPlaceSubmissionInformation::class)->handle(
        $moderator,
        $submission,
        '9f792cff-e798-47de-ad73-5b1ea48d15a2',
        0,
        'contact-evidence-needed',
        'Please provide an official source for the public phone.',
    );
    expect($needsInformation->status)->toBe(PlaceSubmissionStatus::NeedsInformation)
        ->and($needsInformation->lock_version)->toBe(1);

    expect(fn () => app(RejectPlaceSubmission::class)->handle(
        $moderator,
        $needsInformation,
        '2e26861d-0b50-47bf-a18f-b007a84799aa',
        0,
        'insufficient-evidence',
        null,
    ))->toThrow(ValidationException::class);

    $rejected = app(RejectPlaceSubmission::class)->handle(
        $moderator,
        $needsInformation,
        '2e26861d-0b50-47bf-a18f-b007a84799aa',
        1,
        'insufficient-evidence',
        null,
    );
    $reopened = app(ReopenPlaceSubmission::class)->handle(
        $moderator,
        $rejected,
        '0f958141-8e4f-4760-a755-f77f4ab4c3e9',
        2,
        'new-evidence-received',
    );

    expect($reopened->status)->toBe(PlaceSubmissionStatus::Submitted)
        ->and($reopened->lock_version)->toBe(3)
        ->and($reopened->events()->count())->toBe(3);
});

test('a failed merge rolls back source redirect facts submission and audit then succeeds and restores', function () {
    Notification::fake();
    $moderator = User::factory()->administrator()->create();
    $source = Place::factory()->public()->create();
    $destination = Place::factory()->public()->create();
    PlaceFact::factory()->for($source, 'place')->create(['place_submission_id' => null]);
    $submission = PlaceSubmission::factory()->for($this->authenticatedUser, 'submitter')->duplicateReview()->create([
        'published_place_id' => $source->id,
    ]);
    $candidate = PlaceDuplicateCandidate::factory()
        ->for($submission, 'submission')
        ->for($destination, 'candidatePlace')
        ->create();
    PlaceMergeRedirect::creating(static fn () => throw new RuntimeException('forced redirect failure'));

    expect(fn () => app(MergePlaceDuplicate::class)->handle(
        $moderator,
        $submission,
        $source,
        $candidate,
        '83e74c22-05bb-4597-a994-13094104c694',
        0,
        'duplicate-canonical-place',
    ))->toThrow(RuntimeException::class);

    expect($source->fresh()->status)->not->toBe(PlaceStatus::Merged)
        ->and(PlaceMergeRedirect::query()->count())->toBe(0)
        ->and($submission->fresh()->status)->toBe(PlaceSubmissionStatus::DuplicateReview)
        ->and($submission->events()->count())->toBe(0);

    PlaceMergeRedirect::flushEventListeners();
    $merged = app(MergePlaceDuplicate::class)->handle(
        $moderator,
        $submission,
        $source,
        $candidate,
        '83e74c22-05bb-4597-a994-13094104c694',
        0,
        'duplicate-canonical-place',
    );
    expect(PlaceMergeRedirect::query()->count())->toBe(2);
    $mergeEvent = $merged->events()->where('action', 'places-merged')->sole();
    expect(PlaceMergeRedirect::query()->where('place_submission_event_id', $mergeEvent->id)->count())->toBe(2);
    $redirect = PlaceMergeRedirect::query()->orderBy('id')->firstOrFail();
    $restored = app(RestoreMergedPlace::class)->handle(
        $moderator,
        $redirect,
        '6570a209-060f-425a-a5ed-d2b91973e521',
        1,
        'merge-reconsidered',
    );
    $restoreReplay = app(RestoreMergedPlace::class)->handle(
        $moderator,
        $restored,
        '6570a209-060f-425a-a5ed-d2b91973e521',
        1,
        'merge-reconsidered',
    );

    expect($merged->resolution)->toBe(PlaceSubmissionResolution::DuplicateMerge)
        ->and($source->fresh()->merged_into_place_id)->toBeNull()
        ->and($source->fresh()->status)->toBe(PlaceStatus::Active)
        ->and($restored->restored_at)->not->toBeNull()
        ->and($restoreReplay->is($restored))->toBeTrue()
        ->and($merged->events()->where('action', 'merge-restored')->count())->toBe(1)
        ->and($destination->facts()->where('origin_place_id', $source->id)->exists())->toBeTrue();

    $remerged = app(MergePlaceDuplicate::class)->handle(
        $moderator,
        $merged->fresh(),
        $source->fresh(),
        $candidate,
        '59a0b5b1-8d94-45d3-a674-47125af26e3a',
        2,
        'duplicate-confirmed-again',
    );
    $remergeReplay = app(MergePlaceDuplicate::class)->handle(
        $moderator,
        $remerged,
        $source->fresh(),
        $candidate,
        '59a0b5b1-8d94-45d3-a674-47125af26e3a',
        2,
        'duplicate-confirmed-again',
    );

    expect($remergeReplay->is($remerged))->toBeTrue()
        ->and($source->fresh()->status)->toBe(PlaceStatus::Merged)
        ->and(PlaceMergeRedirect::query()->whereNull('restored_at')->count())->toBe(2)
        ->and(PlaceMergeRedirect::query()->whereNotNull('restored_at')->count())->toBe(2)
        ->and($merged->events()->where('action', 'places-merged')->count())->toBe(2);

    Notification::assertSentToTimes($this->authenticatedUser, PlaceSubmissionStatusChanged::class, 3);
});

test('blocked suspended unverified and unrelated accounts cannot submit or inspect another submission', function () {
    $submission = PlaceSubmission::factory()->for($this->authenticatedUser, 'submitter')->create();

    foreach ([
        User::factory()->blocked()->create(),
        User::factory()->suspended()->create(),
        User::factory()->unverified()->create(),
    ] as $actor) {
        expect($actor->can('create', PlaceSubmission::class))->toBeFalse()
            ->and($actor->can('view', $submission))->toBeFalse();
    }

    $unrelated = User::factory()->create();
    expect($unrelated->can('view', $submission))->toBeFalse()
        ->and($unrelated->can('review', $submission))->toBeFalse()
        ->and($this->authenticatedUser->can('view', $submission))->toBeTrue()
        ->and($this->authenticatedUser->can('review', $submission))->toBeFalse();
});

test('dedicated livewire submission validates retains operation identity and isolates account status pages', function () {
    $component = Livewire::actingAs($this->authenticatedUser)
        ->test(CreatePlaceSubmission::class)
        ->assertSet('idempotencyKey', fn (string $value): bool => Str::isUuid($value))
        ->set('form.name', 'Community Training Green')
        ->set('form.catalogCategory', 'park')
        ->set('form.source', 'personal_visit')
        ->set('form.relationshipToPlace', 'visitor')
        ->set('form.locationPrecision', 'public_region')
        ->set('form.publicRegion', 'Vilnius')
        ->set('form.summary', 'A public green used for calm community training sessions.')
        ->set('form.hours', 'Daylight hours')
        ->set('form.services', 'training, water')
        ->set('form.features', 'lighting, benches')
        ->set('form.consentGranted', true);
    $operationKey = $component->get('idempotencyKey');

    $component->call('submit')->assertRedirect();
    $submission = PlaceSubmission::query()->sole();

    expect($submission->idempotency_key)->toBe($operationKey)
        ->and(Place::query()->count())->toBe(0);

    Livewire::actingAs(User::factory()->create())
        ->test(PlaceSubmissionStatusPage::class, ['placeSubmission' => $submission->stable_key])
        ->assertNotFound();
});

test('submission and moderation routes enforce account and reviewer boundaries', function () {
    $submission = PlaceSubmission::factory()->for($this->authenticatedUser, 'submitter')->create();

    $this->get(route('places.submissions.create'))->assertOk();
    $this->get(route('places.submissions.show', $submission))->assertOk();
    $this->get(route('places.moderation.submissions'))->assertForbidden();

    $this->actingAs(User::factory()->create())
        ->get(route('places.submissions.show', $submission))
        ->assertNotFound();

    $administrator = User::factory()->administrator()->create();
    $this->actingAs($administrator)
        ->get(route('places.moderation.submissions'))
        ->assertOk()
        ->assertSee(__('places.submissions.moderation.title'));
});

test('terminal moderation history cannot hide newer actionable submissions', function () {
    $moderator = User::factory()->administrator()->create();
    PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->rejected()
        ->count(50)
        ->sequence(fn ($sequence): array => [
            'name' => 'Historical rejected place '.($sequence->index + 1),
            'submitted_at' => now()->subDays(60)->addMinutes($sequence->index),
        ])
        ->create();
    $actionable = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->create([
            'name' => 'Newest actionable place',
            'submitted_at' => now(),
        ]);

    Livewire::actingAs($moderator)
        ->test(PlaceModerationWorkspace::class)
        ->assertSee($actionable->name);
});

test('completed existing links cannot fill the actionable moderation queue', function () {
    $moderator = User::factory()->administrator()->create();
    $destination = Place::factory()->public()->create();
    $completed = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->count(40)
        ->sequence(fn ($sequence): array => [
            'name' => 'Completed existing link '.($sequence->index + 1),
            'status' => PlaceSubmissionStatus::Published,
            'resolution' => PlaceSubmissionResolution::ExistingLink,
            'linked_place_id' => $destination->id,
            'published_place_id' => null,
            'submitted_at' => now()->subDays(30)->addMinutes($sequence->index),
        ])
        ->create();

    foreach ($completed as $submission) {
        PlaceDuplicateCandidate::factory()
            ->for($submission, 'submission')
            ->for($destination, 'candidatePlace')
            ->create();
    }

    $actionable = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->create([
            'name' => 'Pending after completed links',
            'submitted_at' => now(),
        ]);

    Livewire::actingAs($moderator)
        ->test(PlaceModerationWorkspace::class)
        ->assertSee($actionable->name);
});

test('moderation validation renders a localized recovery summary without changing state', function () {
    $moderator = User::factory()->administrator()->create();
    $submission = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->create();

    Livewire::actingAs($moderator)
        ->test(PlaceModerationWorkspace::class)
        ->call('requestInformation', $submission->stable_key)
        ->assertHasErrors(['reason_detail'])
        ->assertSee(__('places.submissions.validation.summary'));

    expect($submission->fresh()->status)->toBe(PlaceSubmissionStatus::Submitted)
        ->and($submission->events()->count())->toBe(0);
});

test('moderation workspace renders review evidence and reaches merge restore and withdrawn reopen', function () {
    $moderator = User::factory()->administrator()->create();
    $source = Place::factory()->public()->create();
    $destination = Place::factory()->public()->create();
    $submission = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->create([
            'status' => PlaceSubmissionStatus::Published,
            'resolution' => PlaceSubmissionResolution::NewPlace,
            'published_place_id' => $source->id,
            'source_reference' => 'https://example.test/official-place-evidence',
            'relationship_to_place' => 'visitor',
            'location_precision' => PlaceLocationPrecision::PrivateExact,
            'exact_address' => 'Private review entrance 9',
            'submitted_facts' => ['services' => ['water', 'quiet-area']],
        ]);
    $candidate = PlaceDuplicateCandidate::factory()
        ->for($submission, 'submission')
        ->for($destination, 'candidatePlace')
        ->create();
    $withdrawn = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->withdrawn()
        ->create();

    $component = Livewire::actingAs($moderator)
        ->test(PlaceModerationWorkspace::class)
        ->assertSee('https://example.test/official-place-evidence')
        ->assertSee('Private review entrance 9')
        ->assertSee('quiet-area')
        ->assertSee($withdrawn->name)
        ->call('merge', $submission->stable_key, $candidate->candidate_key)
        ->assertHasNoErrors();

    $activeRedirect = PlaceMergeRedirect::query()
        ->where('source_place_id', $source->id)
        ->whereNotNull('active_source_identifier')
        ->firstOrFail();

    $component
        ->call('restore', $submission->stable_key, $activeRedirect->active_source_identifier)
        ->assertHasNoErrors()
        ->call('reopen', $withdrawn->stable_key)
        ->assertHasNoErrors();

    expect($source->fresh()->status)->toBe(PlaceStatus::Active)
        ->and($withdrawn->fresh()->status)->toBe(PlaceSubmissionStatus::Submitted);
});

test('invalid livewire submission writes no aggregate and preserves its operation key', function () {
    $component = Livewire::actingAs($this->authenticatedUser)
        ->test(CreatePlaceSubmission::class)
        ->set('form.name', 'x')
        ->set('form.publicRegion', '')
        ->set('form.consentGranted', false);
    $operationKey = $component->get('idempotencyKey');

    $component->call('submit')
        ->assertHasErrors(['form.name', 'form.publicRegion', 'form.consentGranted'])
        ->assertSet('idempotencyKey', $operationKey);

    expect(PlaceSubmission::query()->count())->toBe(0)
        ->and(PlaceSubmissionEvent::query()->count())->toBe(0);
});

test('protected duplicate status renders generic copy without candidate identity', function () {
    $private = Place::factory()->private()->create([
        'name' => 'Hidden Foster Location',
        'normalized_name' => 'hidden foster location',
    ]);
    $submission = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->duplicateReview()
        ->create();
    PlaceDuplicateCandidate::factory()
        ->for($submission, 'submission')
        ->for($private, 'candidatePlace')
        ->create(['presentation_scope' => 'review_only']);

    Livewire::actingAs($this->authenticatedUser)
        ->test(PlaceSubmissionStatusPage::class, ['placeSubmission' => $submission->stable_key])
        ->assertSee(__('places.submissions.duplicates.protected'))
        ->assertDontSee('Hidden Foster Location');

    expect(fn () => app(ContinueDistinctPlaceSubmission::class)->handle(
        $this->authenticatedUser,
        $submission,
        '1953247f-ce52-479a-9a0f-b21933b869ee',
        0,
    ))->toThrow(NotFoundHttpException::class);
});

test('merged identifiers redirect only when both source and destination are safely visible', function () {
    $source = Place::factory()->public()->create();
    $destination = Place::factory()->public()->create();
    $source->forceFill([
        'status' => PlaceStatus::Merged,
        'merged_into_place_id' => $destination->id,
    ])->save();
    PlaceMergeRedirect::factory()->create([
        'source_place_id' => $source->id,
        'destination_place_id' => $destination->id,
        'source_identifier' => $source->slug,
        'source_visibility' => $source->visibility,
    ]);

    $this->get(route('places.show', ['place' => $source->slug]))
        ->assertRedirect(route('places.show', ['place' => $destination->slug]))
        ->assertHeader('Cache-Control', 'no-store, private');

    $hiddenSource = Place::factory()->private()->create();
    $hiddenSource->forceFill([
        'status' => PlaceStatus::Merged,
        'merged_into_place_id' => $destination->id,
    ])->save();
    PlaceMergeRedirect::factory()->create([
        'source_place_id' => $hiddenSource->id,
        'destination_place_id' => $destination->id,
        'source_identifier' => $hiddenSource->slug,
        'source_visibility' => $hiddenSource->visibility,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('places.show', ['place' => $hiddenSource->slug]))
        ->assertNotFound();

    $hiddenSource->forceFill(['visibility' => PlaceVisibility::Public])->save();

    $this->actingAs(User::factory()->create())
        ->get(route('places.show', ['place' => $hiddenSource->slug]))
        ->assertNotFound();
});

test('chained merges keep original fact provenance and redirect the oldest public identifier', function () {
    $moderator = User::factory()->administrator()->create();
    $first = Place::factory()->public()->create();
    $second = Place::factory()->public()->create();
    $canonical = Place::factory()->public()->create();
    $originFact = PlaceFact::factory()->for($first, 'place')->create([
        'place_submission_id' => null,
        'origin_place_id' => null,
    ]);
    $firstSubmission = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->duplicateReview()
        ->create(['published_place_id' => $first->id]);
    $firstCandidate = PlaceDuplicateCandidate::factory()
        ->for($firstSubmission, 'submission')
        ->for($second, 'candidatePlace')
        ->create();
    app(MergePlaceDuplicate::class)->handle(
        $moderator,
        $firstSubmission,
        $first,
        $firstCandidate,
        'f6d52e10-b668-4f95-b45a-5804ca28e7cc',
        0,
        'first-duplicate-hop',
    );

    $secondSubmission = PlaceSubmission::factory()
        ->for($this->authenticatedUser, 'submitter')
        ->duplicateReview()
        ->create(['published_place_id' => $second->id]);
    $secondCandidate = PlaceDuplicateCandidate::factory()
        ->for($secondSubmission, 'submission')
        ->for($canonical, 'candidatePlace')
        ->create();
    $secondMerged = app(MergePlaceDuplicate::class)->handle(
        $moderator,
        $secondSubmission,
        $second,
        $secondCandidate,
        'df862b49-29ed-4fd5-925f-c17143022ca8',
        0,
        'second-duplicate-hop',
    );

    $canonicalFact = $canonical->facts()->where('value_hash', $originFact->value_hash)->sole();

    $this->get(route('places.show', ['place' => $first->slug]))
        ->assertRedirect(route('places.show', ['place' => $canonical->slug]));

    expect($first->fresh()->merged_into_place_id)->toBe($canonical->id)
        ->and($canonicalFact->origin_place_id)->toBe($first->id)
        ->and($canonicalFact->copiedFrom->place_id)->toBe($second->id)
        ->and(PlaceMergeRedirect::query()
            ->where('source_place_id', $first->id)
            ->where('destination_place_id', $canonical->id)
            ->whereNotNull('active_source_identifier')
            ->count())->toBe(2);

    $secondRedirect = PlaceMergeRedirect::query()
        ->where('source_place_id', $second->id)
        ->where('destination_place_id', $canonical->id)
        ->whereNotNull('active_source_identifier')
        ->firstOrFail();
    app(RestoreMergedPlace::class)->handle(
        $moderator,
        $secondRedirect,
        '36da826d-ce42-438d-8876-ed5bc4649825',
        $secondMerged->lock_version,
        'restore-second-hop',
    );

    $this->get(route('places.show', ['place' => $first->slug]))
        ->assertRedirect(route('places.show', ['place' => $second->slug]));

    expect($second->fresh()->status)->toBe(PlaceStatus::Active)
        ->and($first->fresh()->merged_into_place_id)->toBe($second->id)
        ->and(PlaceMergeRedirect::query()
            ->where('source_place_id', $first->id)
            ->where('destination_place_id', $second->id)
            ->whereNotNull('active_source_identifier')
            ->count())->toBe(2)
        ->and(PlaceMergeRedirect::query()
            ->where('source_place_id', $first->id)
            ->where('destination_place_id', $canonical->id)
            ->whereNotNull('active_source_identifier')
            ->count())->toBe(0);
});

test('validation rejects inconsistent locations contacts consent and future evidence without writes', function () {
    $action = app(SubmitPlaceSubmission::class);
    $invalid = [
        placeSubmissionData('10000000-0000-4000-8000-000000000001', publicLatitude: null),
        placeSubmissionData(
            '10000000-0000-4000-8000-000000000002',
            locationPrecision: PlaceLocationPrecision::PublicRegion,
        ),
        placeSubmissionData(
            '10000000-0000-4000-8000-000000000009',
            locationPrecision: PlaceLocationPrecision::PublicRegion,
            publicLatitude: null,
            publicLongitude: null,
        ),
        placeSubmissionData(
            '10000000-0000-4000-8000-000000000003',
            locationPrecision: PlaceLocationPrecision::PrivateExact,
            publicLatitude: null,
            publicLongitude: null,
        ),
        placeSubmissionData('10000000-0000-4000-8000-000000000004', publicPhone: '123'),
        placeSubmissionData('10000000-0000-4000-8000-000000000010', publicPhone: 'call-me-at-12345678'),
        placeSubmissionData('10000000-0000-4000-8000-000000000005', consentGranted: false),
        placeSubmissionData('10000000-0000-4000-8000-000000000006', observedAt: now()->addDays(3)->toImmutable()),
    ];

    foreach ($invalid as $data) {
        expect(fn () => $action->handle($this->authenticatedUser, $data))
            ->toThrow(ValidationException::class);
    }

    expect(PlaceSubmission::query()->count())->toBe(0)
        ->and(PlaceFact::query()->count())->toBe(0)
        ->and(PlaceSubmissionEvent::query()->count())->toBe(0);
});

test('category rules require operational emergency facts while general-area routes remain address optional', function () {
    $action = app(SubmitPlaceSubmission::class);

    expect(fn () => $action->handle(
        $this->authenticatedUser,
        placeSubmissionData(
            '10000000-0000-4000-8000-000000000007',
            publicPhone: null,
            catalogCategory: 'emergency-vet',
            facts: ['services' => ['urgent-care']],
        ),
    ))->toThrow(ValidationException::class);

    $route = $action->handle(
        $this->authenticatedUser,
        placeSubmissionData(
            '10000000-0000-4000-8000-000000000008',
            name: 'Neris riverside walking route',
            locationPrecision: PlaceLocationPrecision::PublicRegion,
            publicLatitude: null,
            publicLongitude: null,
            publicPhone: null,
            publicAddress: null,
            catalogCategory: 'route',
            type: PlaceType::WalkingRoute,
            facts: ['features' => ['riverside']],
        ),
    );

    expect($route->catalog_category)->toBe('route')
        ->and($route->public_address)->toBeNull()
        ->and($route->public_latitude)->toBeNull();
});

test('submission rejects unknown fact keys without writing review records', function () {
    $exception = null;

    try {
        app(SubmitPlaceSubmission::class)->handle(
            $this->authenticatedUser,
            placeSubmissionData(
                '10000000-0000-4000-8000-000000000011',
                facts: [
                    'services' => ['preventive-care'],
                    'unreviewed_private_hint' => 'Do not persist this.',
                ],
            ),
        );
    } catch (ValidationException $caught) {
        $exception = $caught;
    }

    expect($exception)->toBeInstanceOf(ValidationException::class)
        ->and($exception?->errors())->toHaveKey('facts')
        ->and($exception?->errors()['facts'][0])->toBe(__('places.submissions.validation.unknown_fact'))
        ->and(PlaceSubmission::query()->count())->toBe(0)
        ->and(PlaceFact::query()->count())->toBe(0)
        ->and(PlaceSubmissionEvent::query()->count())->toBe(0);
});

test('an active candidate place manager may link but cannot approve reject merge or review another scope', function () {
    $manager = User::factory()->create();
    $ordinary = User::factory()->create();
    $organization = Organization::factory()->create();
    OrganizationMembership::factory()->active()->create([
        'organization_id' => $organization->id,
        'user_id' => $manager->id,
        'role' => OrganizationRole::EventManager,
    ]);
    OrganizationMembership::factory()->active()->create([
        'organization_id' => $organization->id,
        'user_id' => $ordinary->id,
        'role' => OrganizationRole::Member,
    ]);
    $place = Place::factory()->public()->create([
        'owner_user_id' => null,
        'organization_id' => $organization->id,
    ]);
    $submission = PlaceSubmission::factory()->for($this->authenticatedUser, 'submitter')->duplicateReview()->create();
    $candidate = PlaceDuplicateCandidate::factory()
        ->for($submission, 'submission')
        ->for($place, 'candidatePlace')
        ->create();

    expect($manager->can('linkExisting', [$submission, $candidate]))->toBeTrue()
        ->and($manager->can('requestInformation', [$submission, $candidate]))->toBeTrue()
        ->and($manager->can('view', $submission))->toBeTrue()
        ->and($manager->can('review', $submission))->toBeTrue()
        ->and($manager->can('approveNewPlace', $submission))->toBeFalse()
        ->and($manager->can('reject', $submission))->toBeFalse()
        ->and($manager->can('merge', $submission))->toBeFalse()
        ->and($ordinary->can('linkExisting', [$submission, $candidate]))->toBeFalse();

    $organization->forceFill(['status' => OrganizationStatus::Suspended])->save();
    expect($manager->can('linkExisting', [$submission, $candidate]))->toBeFalse();
});

function placeSubmissionData(
    string $idempotencyKey,
    string $name = 'Žvėryno Pet Clinic',
    PlaceLocationPrecision $locationPrecision = PlaceLocationPrecision::PublicPoint,
    ?string $publicLatitude = '54.687210',
    ?string $publicLongitude = '25.279710',
    ?string $exactAddress = null,
    ?string $exactLatitude = null,
    ?string $exactLongitude = null,
    ?string $publicPhone = '+370 (612) 34-567',
    bool $consentGranted = true,
    ?CarbonImmutable $observedAt = null,
    ?string $publicAddress = 'Gedimino pr. 12, Vilnius',
    string $catalogCategory = 'vet',
    PlaceType $type = PlaceType::VeterinaryClinic,
    ?array $facts = null,
): SubmitPlaceData {
    return new SubmitPlaceData(
        name: $name,
        type: $type,
        catalogCategory: $catalogCategory,
        source: PlaceSubmissionSource::PersonalVisit,
        sourceReference: 'https://example.lt/places?utm_source=community',
        relationshipToPlace: 'visitor',
        locationPrecision: $locationPrecision,
        locale: 'en',
        publicRegion: 'Vilnius',
        publicAddress: $publicAddress,
        publicLatitude: $publicLatitude,
        publicLongitude: $publicLongitude,
        exactAddress: $exactAddress,
        exactLatitude: $exactLatitude,
        exactLongitude: $exactLongitude,
        publicPhone: $publicPhone,
        publicEmail: 'INFO@EXAMPLE.LT',
        publicWebsite: 'https://www.example.lt/places/?utm_source=community#hours',
        summary: 'A community-observed veterinary clinic with a public entrance.',
        facts: $facts ?? [
            'hours' => ['monday' => '08:00-20:00'],
            'services' => ['preventive-care', 'urgent-care'],
            'rules' => 'Call before arriving with an emergency.',
            'features' => ['step-free-entrance'],
        ],
        canonicalOrganizationId: null,
        observedAt: $observedAt ?? now()->subDay()->toImmutable(),
        consentVersion: 'places-submission-v1',
        consentGranted: $consentGranted,
        idempotencyKey: $idempotencyKey,
        auditContext: [
            'channel' => 'place-submission-test',
            'request_id' => 'request-places-001',
        ],
    );
}
