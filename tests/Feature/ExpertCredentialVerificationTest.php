<?php

declare(strict_types=1);

use App\Actions\ReviewCredentialVerificationAppeal;
use App\Actions\ReviewProfessionalCredential;
use App\Actions\SubmitCredentialVerificationAppeal;
use App\Enums\CredentialStatus;
use App\Enums\VerificationStatus;
use App\Models\Credential;
use App\Models\CredentialVerificationEvent;
use App\Models\ExpertProfile;
use App\Models\ForumReputationEvent;
use App\Models\User;
use App\Policies\CredentialPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

test('credential evidence remains private and expiration is derived without a scheduler', function () {
    $credential = Credential::factory()->expired()->create([
        'verification_notes' => ['private' => 'reviewer evidence'],
        'metadata' => ['private' => 'source response'],
    ]);

    expect($credential->effectiveStatus())->toBe(CredentialStatus::Expired)
        ->and($credential->toArray())->not->toHaveKeys([
            'credential_identifier_hash',
            'file_path',
            'verification_notes',
            'metadata',
        ])
        ->and($credential->status->label())->toBe(__('credential_verification.status.verified'))
        ->and($credential->effectiveStatus()->label())->toBe(__('credential_verification.status.expired'));
});

test('credential policy separates owner evidence access from independent review', function () {
    $owner = User::factory()->create();
    $administrator = User::factory()->administrator()->create();
    $profile = ExpertProfile::factory()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
    ]);
    $credential = Credential::factory()->suspended()->create([
        'expert_profile_id' => $profile->id,
        'reviewer_user_id' => $administrator->id,
    ]);
    $policy = app(CredentialPolicy::class);

    expect($policy->view($owner, $credential))->toBeTrue()
        ->and($policy->appeal($owner, $credential))->toBeTrue()
        ->and($policy->review($owner, $credential))->toBeFalse()
        ->and($policy->review($administrator, $credential))->toBeTrue()
        ->and($policy->view(User::factory()->create(), $credential))->toBeFalse();
});

test('independent review is transactional idempotent audited and unrelated to karma', function () {
    $owner = User::factory()->create();
    $reviewer = User::factory()->administrator()->create();
    $profile = ExpertProfile::factory()->unverified()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
    ]);
    $credential = Credential::factory()->submitted()->create([
        'expert_profile_id' => $profile->id,
        'expires_at' => now()->addYear(),
    ]);
    $review = app(ReviewProfessionalCredential::class);

    $review->handle(
        $reviewer,
        $credential->id,
        CredentialStatus::InReview,
        'credential_verification.reason.information-required',
        'The credential has entered an independent issuing-authority review.',
        'credential-review-start-'.$credential->id,
    );
    $verified = $review->handle(
        $reviewer,
        $credential->id,
        CredentialStatus::Verified,
        'credential_verification.reason.approved',
        'The issuing authority independently confirmed the credential and scope.',
        'credential-review-approved-'.$credential->id,
    );
    $duplicate = $review->handle(
        $reviewer,
        $credential->id,
        CredentialStatus::Verified,
        'credential_verification.reason.approved',
        'The issuing authority independently confirmed the credential and scope.',
        'credential-review-approved-'.$credential->id,
    );

    expect($verified->status)->toBe(CredentialStatus::Verified)
        ->and($duplicate->id)->toBe($verified->id)
        ->and($profile->refresh()->verification_status)->toBe(VerificationStatus::Verified)
        ->and(CredentialVerificationEvent::query()->count())->toBe(2)
        ->and(ForumReputationEvent::query()->count())->toBe(0);
});

test('reviewer cannot verify their own professional profile', function () {
    $administrator = User::factory()->administrator()->create();
    $profile = ExpertProfile::factory()->unverified()->create([
        'owner_id' => $administrator->id,
        'owner_key' => $administrator->actor_key,
    ]);
    $credential = Credential::factory()->submitted()->create([
        'expert_profile_id' => $profile->id,
    ]);

    expect(fn () => app(ReviewProfessionalCredential::class)->handle(
        $administrator,
        $credential->id,
        CredentialStatus::InReview,
        'credential_verification.reason.information-required',
        'This review must not be performed by the profile owner.',
        'self-review-'.$credential->id,
    ))->toThrow(AuthorizationException::class);
});

test('appeal requires an independent reviewer and restores a valid prior status', function () {
    $owner = User::factory()->create();
    $originalReviewer = User::factory()->administrator()->create();
    $appealReviewer = User::factory()->administrator()->create();
    $profile = ExpertProfile::factory()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
    ]);
    $credential = Credential::factory()->create([
        'expert_profile_id' => $profile->id,
        'reviewer_user_id' => $originalReviewer->id,
        'expires_at' => now()->addYear(),
    ]);
    app(ReviewProfessionalCredential::class)->handle(
        $originalReviewer,
        $credential->id,
        CredentialStatus::Suspended,
        'credential_verification.reason.suspended',
        'A discrepancy requires independent review before the badge remains visible.',
        'credential-suspend-'.$credential->id,
    );
    $statement = 'The issuing authority corrected the discrepancy and supplied a signed confirmation.';
    $appeal = app(SubmitCredentialVerificationAppeal::class)->handle(
        $owner,
        $credential->id,
        $statement,
    );

    expect(fn () => app(SubmitCredentialVerificationAppeal::class)->handle(
        $owner,
        $credential->id,
        $statement,
    ))->toThrow(ValidationException::class)
        ->and(fn () => app(ReviewCredentialVerificationAppeal::class)->handle(
            $originalReviewer,
            $appeal->id,
            'reversed',
            'The supplied correction is sufficient to reverse the decision.',
            'appeal-original-reviewer-'.$appeal->id,
        ))->toThrow(ValidationException::class);

    $resolved = app(ReviewCredentialVerificationAppeal::class)->handle(
        $appealReviewer,
        $appeal->id,
        'reversed',
        'The independent correction confirms the original verified state.',
        'appeal-reversed-'.$appeal->id,
    );

    expect($resolved->status)->toBe('reversed')
        ->and($credential->refresh()->status)->toBe(CredentialStatus::Verified)
        ->and($profile->refresh()->verification_status)->toBe(VerificationStatus::Verified);
});

test('credential verification event history cannot be rewritten', function () {
    $event = CredentialVerificationEvent::factory()->create();

    expect(fn () => $event->update(['event_type' => 'rewritten']))
        ->toThrow(LogicException::class)
        ->and(fn () => $event->delete())
        ->toThrow(LogicException::class);
});
