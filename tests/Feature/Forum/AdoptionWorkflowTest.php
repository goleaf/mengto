<?php

declare(strict_types=1);

use App\Actions\CloseAdoptionCase;
use App\Actions\ReviewCredentialVerificationAppeal;
use App\Actions\ReviewProfessionalCredential;
use App\Actions\SubmitAdoptionApplication;
use App\Actions\SubmitCredentialVerificationAppeal;
use App\Actions\SynchronizeAdoptionCase;
use App\Actions\TransitionAdoptionApplication;
use App\Data\AdoptionApplicationData;
use App\Enums\AdoptionApplicationStatus;
use App\Enums\AdoptionCaseStatus;
use App\Enums\AdoptionPlacementType;
use App\Enums\AdoptionProviderIdentityStatus;
use App\Enums\CredentialStatus;
use App\Enums\CredentialType;
use App\Enums\ListingStatus;
use App\Enums\SellerType;
use App\Livewire\Forum\AdoptionWorkflow;
use App\Models\AdoptionApplication;
use App\Models\AdoptionCase;
use App\Models\AdoptionEvent;
use App\Models\Credential;
use App\Models\DomesticClassification;
use App\Models\ExpertProfile;
use App\Models\Listing;
use App\Models\Taxon;
use App\Models\User;
use Database\Seeders\AdoptionCaseSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

test('existing adoption listings synchronize once without exposing a precise area or guessing taxonomy', function () {
    $listing = Listing::factory()->adoption()->create([
        'city' => 'Vilnius',
        'area' => 'Exact private street area',
        'status' => ListingStatus::Published,
        'attributes' => [
            'animal_name' => 'Luna',
            'animal_age' => 'Three years',
            'animal_sex' => 'female',
            'temperament' => 'Calm indoors.',
            'adoption_conditions' => 'Indoor home required.',
        ],
    ]);
    $synchronize = app(SynchronizeAdoptionCase::class);

    $first = $synchronize->handle($listing);
    $second = $synchronize->handle($listing);

    expect($first)
        ->not->toBeNull()
        ->id->toBe($second?->id)
        ->listing_id->toBe($listing->id)
        ->case_number->toBe('ADP-'.str_pad((string) $listing->id, 10, '0', STR_PAD_LEFT))
        ->public_location->toBe('Vilnius')
        ->taxon_id->toBeNull()
        ->domestic_classification_id->toBeNull()
        ->and(AdoptionCase::query()->where('listing_id', $listing->id)->count())->toBe(1)
        ->and(AdoptionEvent::query()->where('adoption_case_id', $first?->id)->count())->toBe(1)
        ->and($first?->toArray())->not->toContain('Exact private street area');
});

test('adoption case seeding is idempotent and preserves listing identity', function () {
    $listing = Listing::factory()->adoption()->create();

    $this->seed(AdoptionCaseSeeder::class);
    $case = AdoptionCase::query()->where('listing_id', $listing->id)->firstOrFail();
    $this->seed(AdoptionCaseSeeder::class);

    expect(AdoptionCase::query()->where('listing_id', $listing->id)->count())->toBe(1)
        ->and(AdoptionCase::query()->where('listing_id', $listing->id)->value('id'))->toBe($case->id)
        ->and($listing->fresh()?->id)->toBe($listing->id);
});

test('private provider verification requires a current identity credential owned by the listing provider', function () {
    $owner = User::factory()->create();
    $listing = Listing::factory()->adoption()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
        'seller_type' => SellerType::PrivateSeller,
        'is_verified_seller' => true,
    ]);
    $profile = ExpertProfile::factory()->unverified()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
    ]);
    $credential = Credential::factory()->create([
        'expert_profile_id' => $profile->id,
        'type' => CredentialType::Identity->value,
        'status' => CredentialStatus::Verified,
        'expires_at' => now()->addYear(),
    ]);

    $case = app(SynchronizeAdoptionCase::class)->handle($listing);
    app(SynchronizeAdoptionCase::class)->handle($listing);

    expect($case)
        ->not->toBeNull()
        ->provider_expert_profile_id->toBe($profile->id)
        ->provider_credential_id->toBe($credential->id)
        ->provider_identity_status->toBe(AdoptionProviderIdentityStatus::Verified)
        ->provider_verified->toBeTrue()
        ->and($case?->providerCredential?->toArray())->not->toHaveKeys([
            'credential_identifier_hash',
            'file_path',
            'verification_notes',
            'metadata',
        ])
        ->and(AdoptionEvent::query()
            ->where('adoption_case_id', $case?->id)
            ->where('event_type', 'provider-identity-status-changed')
            ->count())->toBe(1);
});

test('legacy seller trust and unrelated or foreign credentials cannot verify an adoption provider', function () {
    $owner = User::factory()->create();
    $otherOwner = User::factory()->create();
    $listing = Listing::factory()->adoption()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
        'seller_type' => SellerType::Shelter,
        'is_verified_seller' => true,
    ]);
    $ownerProfile = ExpertProfile::factory()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
    ]);
    Credential::factory()->create([
        'expert_profile_id' => $ownerProfile->id,
        'type' => CredentialType::License->value,
    ]);
    $foreignProfile = ExpertProfile::factory()->create([
        'owner_id' => $otherOwner->id,
        'owner_key' => $otherOwner->actor_key,
    ]);
    Credential::factory()->create([
        'expert_profile_id' => $foreignProfile->id,
        'type' => CredentialType::Shelter->value,
    ]);

    $case = app(SynchronizeAdoptionCase::class)->handle($listing);

    expect($case)
        ->not->toBeNull()
        ->provider_credential_id->toBeNull()
        ->provider_identity_status->toBe(AdoptionProviderIdentityStatus::Unverified)
        ->provider_verified->toBeFalse();
});

test('independent organization credential review propagates pending verified suspended and revoked states', function () {
    $owner = User::factory()->create();
    $reviewer = User::factory()->administrator()->create();
    $listing = Listing::factory()->adoption()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
        'seller_type' => SellerType::Shelter,
    ]);
    $profile = ExpertProfile::factory()->unverified()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
    ]);
    $credential = Credential::factory()->submitted()->create([
        'expert_profile_id' => $profile->id,
        'type' => CredentialType::OrganizationRole->value,
        'expires_at' => now()->addYear(),
    ]);
    $case = app(SynchronizeAdoptionCase::class)->handle($listing);
    $review = app(ReviewProfessionalCredential::class);

    expect($case?->provider_identity_status)->toBe(AdoptionProviderIdentityStatus::Pending);

    $review->handle(
        $reviewer,
        $credential->id,
        CredentialStatus::InReview,
        'credential_verification.reason.information-required',
        'The organization relationship entered independent documentary review.',
        'adoption-provider-review-'.$credential->id,
    );
    $review->handle(
        $reviewer,
        $credential->id,
        CredentialStatus::Verified,
        'credential_verification.reason.approved',
        'The issuing organization independently confirmed the provider relationship.',
        'adoption-provider-verified-'.$credential->id,
    );

    expect($case?->refresh())
        ->provider_identity_status->toBe(AdoptionProviderIdentityStatus::Verified)
        ->provider_verified->toBeTrue()
        ->and($profile->refresh()->organization_verified)->toBeTrue();

    $review->handle(
        $reviewer,
        $credential->id,
        CredentialStatus::Suspended,
        'credential_verification.reason.suspended',
        'A new discrepancy requires the provider relationship to be reviewed again.',
        'adoption-provider-suspended-'.$credential->id,
    );

    expect($case?->refresh())
        ->provider_identity_status->toBe(AdoptionProviderIdentityStatus::Suspended)
        ->provider_verified->toBeFalse()
        ->and($profile->refresh()->organization_verified)->toBeFalse();

    $review->handle(
        $reviewer,
        $credential->id,
        CredentialStatus::Revoked,
        'credential_verification.reason.revoked',
        'The issuing organization withdrew the evidence supporting this provider relationship.',
        'adoption-provider-revoked-'.$credential->id,
    );

    expect($case?->refresh())
        ->provider_identity_status->toBe(AdoptionProviderIdentityStatus::Revoked)
        ->provider_verified->toBeFalse()
        ->and($profile->refresh()->organization_verified)->toBeFalse();
});

test('rejected provider evidence remains unverified and appeal reversal restores the independent projection', function () {
    $owner = User::factory()->create();
    $originalReviewer = User::factory()->administrator()->create();
    $appealReviewer = User::factory()->administrator()->create();
    $listing = Listing::factory()->adoption()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
        'seller_type' => SellerType::Shelter,
    ]);
    $profile = ExpertProfile::factory()->unverified()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
    ]);
    $credential = Credential::factory()->submitted()->create([
        'expert_profile_id' => $profile->id,
        'type' => CredentialType::OrganizationRegistration->value,
        'expires_at' => now()->addYear(),
    ]);
    $review = app(ReviewProfessionalCredential::class);
    $case = app(SynchronizeAdoptionCase::class)->handle($listing);

    $review->handle(
        $originalReviewer,
        $credential->id,
        CredentialStatus::Rejected,
        'credential_verification.reason.rejected',
        'The registration evidence could not be independently matched to the issuing organization.',
        'adoption-provider-rejected-'.$credential->id,
    );

    expect($case?->refresh())
        ->provider_identity_status->toBe(AdoptionProviderIdentityStatus::Rejected)
        ->provider_verified->toBeFalse();

    $review->handle(
        $originalReviewer,
        $credential->id,
        CredentialStatus::Submitted,
        'credential_verification.reason.renewed',
        'The provider supplied corrected registration evidence for a new independent review.',
        'adoption-provider-resubmitted-'.$credential->id,
    );
    $review->handle(
        $originalReviewer,
        $credential->id,
        CredentialStatus::InReview,
        'credential_verification.reason.information-required',
        'The corrected organization registration entered independent review.',
        'adoption-provider-second-review-'.$credential->id,
    );
    $review->handle(
        $originalReviewer,
        $credential->id,
        CredentialStatus::Verified,
        'credential_verification.reason.approved',
        'The corrected registration was confirmed directly with the issuing organization.',
        'adoption-provider-second-verified-'.$credential->id,
    );
    $review->handle(
        $originalReviewer,
        $credential->id,
        CredentialStatus::Suspended,
        'credential_verification.reason.suspended',
        'A later discrepancy requires an independent appeal review.',
        'adoption-provider-second-suspended-'.$credential->id,
    );

    $appeal = app(SubmitCredentialVerificationAppeal::class)->handle(
        $owner,
        $credential->id,
        'The issuing organization supplied a signed correction confirming that the registration remains valid.',
    );
    app(ReviewCredentialVerificationAppeal::class)->handle(
        $appealReviewer,
        $appeal->id,
        'reversed',
        'Independent review confirmed the correction and restored the prior verified status.',
        'adoption-provider-appeal-reversed-'.$appeal->id,
    );

    expect($case?->refresh())
        ->provider_identity_status->toBe(AdoptionProviderIdentityStatus::Verified)
        ->provider_verified->toBeTrue()
        ->and($profile->refresh()->organization_verified)->toBeTrue();
});

test('provider verification expiration is reflected without a scheduler or stored flag rewrite', function () {
    $owner = User::factory()->create();
    $listing = Listing::factory()->adoption()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
        'seller_type' => SellerType::PrivateSeller,
    ]);
    $profile = ExpertProfile::factory()->unverified()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
    ]);
    Credential::factory()->create([
        'expert_profile_id' => $profile->id,
        'type' => CredentialType::Identity->value,
        'expires_at' => now()->addDay(),
    ]);
    $case = app(SynchronizeAdoptionCase::class)->handle($listing);

    expect($case?->effectiveProviderIdentityStatus())
        ->toBe(AdoptionProviderIdentityStatus::Verified);

    $this->travel(2)->days();

    expect($case?->refresh()->provider_identity_status)
        ->toBe(AdoptionProviderIdentityStatus::Verified)
        ->and($case?->effectiveProviderIdentityStatus())
        ->toBe(AdoptionProviderIdentityStatus::Expired);

    Livewire::test(AdoptionWorkflow::class, ['listingId' => $listing->id])
        ->assertSee(__('adoption.identity_status.expired'))
        ->assertDontSee(__('adoption.identity_status.verified'));
});

test('private applications are encrypted hidden and absent from the public listing', function () {
    [$owner, $applicant, $case] = adoptionActorsAndCase();
    $secret = 'Private landlord approval reference 92-ALPHA';
    $data = adoptionApplicationData(['home_context' => $secret]);

    $application = app(SubmitAdoptionApplication::class)->handle(
        $applicant,
        $case,
        $data,
        (string) Str::uuid(),
    );

    expect($application->private_profile['home_context'])->toBe($secret)
        ->and($application->getRawOriginal('private_profile'))->not->toContain($secret)
        ->and($application->toArray())->not->toHaveKey('private_profile')
        ->and($application->toArray())->not->toHaveKey('private_references');

    $this->get(route('marketplace.show', $case->listing))
        ->assertOk()
        ->assertDontSee($secret);

    $this->actingAs($owner)
        ->get(route('marketplace.show', $case->listing))
        ->assertOk()
        ->assertDontSee($secret);
});

test('application submission is idempotent and rejects a second application for the same case', function () {
    [, $applicant, $case] = adoptionActorsAndCase();
    $action = app(SubmitAdoptionApplication::class);
    $key = (string) Str::uuid();

    $first = $action->handle($applicant, $case, adoptionApplicationData(), $key);
    $same = $action->handle($applicant, $case, adoptionApplicationData(), $key);

    expect($same->id)->toBe($first->id)
        ->and(AdoptionApplication::query()->count())->toBe(1)
        ->and(fn () => $action->handle(
            $applicant,
            $case,
            adoptionApplicationData(),
            (string) Str::uuid(),
        ))->toThrow(ValidationException::class)
        ->and(AdoptionApplication::query()->count())->toBe(1);
});

test('listing owner can transition an application and stale versions are rejected', function () {
    [$owner, $applicant, $case] = adoptionActorsAndCase();
    $application = AdoptionApplication::factory()->create([
        'adoption_case_id' => $case->id,
        'applicant_user_id' => $applicant->id,
        'status' => AdoptionApplicationStatus::Submitted,
        'lock_version' => 1,
    ]);
    $action = app(TransitionAdoptionApplication::class);

    $updated = $action->handle(
        $owner,
        $application,
        AdoptionApplicationStatus::Screening,
        1,
    );

    expect($updated)
        ->status->toBe(AdoptionApplicationStatus::Screening)
        ->lock_version->toBe(2)
        ->reviewer_user_id->toBe($owner->id)
        ->and($case->refresh()->status)->toBe(AdoptionCaseStatus::Screening)
        ->and(AdoptionEvent::query()
            ->where('adoption_application_id', $application->id)
            ->where('event_type', 'application-status-changed')
            ->exists())->toBeTrue()
        ->and(fn () => $action->handle(
            $owner,
            $updated,
            AdoptionApplicationStatus::HomeCheck,
            1,
        ))->toThrow(ValidationException::class);
});

test('adoption placement supports every review trial adoption and follow up stage', function () {
    [$owner, $applicant, $case] = adoptionActorsAndCase();
    $application = AdoptionApplication::factory()->create([
        'adoption_case_id' => $case->id,
        'applicant_user_id' => $applicant->id,
        'status' => AdoptionApplicationStatus::Submitted,
    ]);
    $action = app(TransitionAdoptionApplication::class);
    $path = [
        AdoptionApplicationStatus::Screening,
        AdoptionApplicationStatus::HomeCheck,
        AdoptionApplicationStatus::References,
        AdoptionApplicationStatus::Meeting,
        AdoptionApplicationStatus::Reserved,
        AdoptionApplicationStatus::ContractPending,
        AdoptionApplicationStatus::Trial,
        AdoptionApplicationStatus::Adopted,
        AdoptionApplicationStatus::FollowUp,
        AdoptionApplicationStatus::Closed,
    ];

    foreach ($path as $target) {
        $application = $action->handle(
            $owner,
            $application,
            $target,
            $application->lock_version,
        );
    }

    expect($application)
        ->status->toBe(AdoptionApplicationStatus::Closed)
        ->reviewed_at->not->toBeNull()
        ->meeting_at->not->toBeNull()
        ->reserved_at->not->toBeNull()
        ->contracted_at->not->toBeNull()
        ->trial_started_at->not->toBeNull()
        ->follow_up_at->not->toBeNull()
        ->closed_at->not->toBeNull()
        ->and($case->refresh()->status)->toBe(AdoptionCaseStatus::Adopted)
        ->and(AdoptionEvent::query()
            ->where('adoption_application_id', $application->id)
            ->where('event_type', 'application-status-changed')
            ->count())->toBe(count($path));
});

test('foster transfers returns and failed placements follow controlled recovery paths', function () {
    [$owner, $applicant, $case] = adoptionActorsAndCase();
    $action = app(TransitionAdoptionApplication::class);
    $foster = AdoptionApplication::factory()->create([
        'adoption_case_id' => $case->id,
        'applicant_user_id' => $applicant->id,
        'status' => AdoptionApplicationStatus::Meeting,
    ]);

    foreach ([
        AdoptionApplicationStatus::FosterPlaced,
        AdoptionApplicationStatus::Transferred,
        AdoptionApplicationStatus::FosterPlaced,
        AdoptionApplicationStatus::Returned,
        AdoptionApplicationStatus::Screening,
    ] as $target) {
        $foster = $action->handle($owner, $foster, $target, $foster->lock_version);
    }

    $failed = AdoptionApplication::factory()->create([
        'adoption_case_id' => AdoptionCase::factory()->create([
            'listing_id' => Listing::factory()->adoption()->create([
                'owner_id' => $owner->id,
                'owner_key' => $owner->actor_key,
            ])->id,
        ])->id,
        'applicant_user_id' => User::factory()->create()->id,
        'status' => AdoptionApplicationStatus::ContractPending,
    ]);
    $failed = $action->handle(
        $owner,
        $failed,
        AdoptionApplicationStatus::Failed,
        $failed->lock_version,
    );
    $failed = $action->handle(
        $owner,
        $failed,
        AdoptionApplicationStatus::Screening,
        $failed->lock_version,
    );

    expect($foster->status)->toBe(AdoptionApplicationStatus::Screening)
        ->and($case->refresh()->status)->toBe(AdoptionCaseStatus::Screening)
        ->and($failed->status)->toBe(AdoptionApplicationStatus::Screening)
        ->and($failed->closed_at)->toBeNull();
});

test('applicants may withdraw but cannot approve their own application', function () {
    [, $applicant, $case] = adoptionActorsAndCase();
    $application = AdoptionApplication::factory()->create([
        'adoption_case_id' => $case->id,
        'applicant_user_id' => $applicant->id,
        'status' => AdoptionApplicationStatus::Submitted,
    ]);
    $action = app(TransitionAdoptionApplication::class);

    expect(fn () => $action->handle(
        $applicant,
        $application,
        AdoptionApplicationStatus::Screening,
        1,
    ))->toThrow(ValidationException::class);

    $withdrawn = $action->handle(
        $applicant,
        $application,
        AdoptionApplicationStatus::Withdrawn,
        1,
    );

    expect($withdrawn->status)->toBe(AdoptionApplicationStatus::Withdrawn)
        ->and($withdrawn->reviewer_user_id)->toBeNull();
});

test('unrelated and blocked users cannot inspect or mutate private applications', function () {
    [$owner, $applicant, $case] = adoptionActorsAndCase();
    $application = AdoptionApplication::factory()->create([
        'adoption_case_id' => $case->id,
        'applicant_user_id' => $applicant->id,
    ]);
    $unrelated = User::factory()->create();
    $blocked = User::factory()->blocked()->create();

    expect($unrelated->can('view', $application))->toBeFalse()
        ->and($blocked->can('view', $application))->toBeFalse()
        ->and($owner->can('view', $application))->toBeTrue()
        ->and($applicant->can('view', $application))->toBeTrue()
        ->and(fn () => app(TransitionAdoptionApplication::class)->handle(
            $unrelated,
            $application,
            AdoptionApplicationStatus::Screening,
            1,
        ))->toThrow(AuthorizationException::class);

    Livewire::actingAs($unrelated)
        ->test(AdoptionWorkflow::class, ['listingId' => $case->listing_id])
        ->call('selectApplication', $application->id)
        ->assertForbidden();
});

test('livewire validates submits and authorizes direct adoption actions', function () {
    [, $applicant, $case] = adoptionActorsAndCase();

    Livewire::actingAs($applicant)
        ->test(AdoptionWorkflow::class, ['listingId' => $case->listing_id])
        ->call('submit')
        ->assertHasErrors([
            'form.message',
            'form.experience',
            'form.homeContext',
            'form.household',
            'form.carePlan',
            'form.placementReason',
            'form.transportPlan',
            'form.termsAccepted',
            'form.privacyAccepted',
        ])
        ->set('form.placementType', 'adoption')
        ->set('form.message', 'We would like to complete the full welfare-focused application process.')
        ->set('form.experience', 'We have several years of calm indoor adult animal care experience.')
        ->set('form.homeContext', 'Our apartment permits animals and has secure windows and quiet rooms.')
        ->set('form.household', 'Both adults in the household agree to the placement.')
        ->set('form.otherAnimals', 'There are no resident animals in the home.')
        ->set('form.carePlan', 'We have planned daily care, enrichment, insurance, and emergency savings.')
        ->set('form.placementReason', 'The published needs and temperament fit our stable household routine.')
        ->set('form.transportPlan', 'We can attend two meetings and use a secure carrier.')
        ->set('form.termsAccepted', true)
        ->set('form.privacyAccepted', true)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSee('Your private application was submitted.');

    expect(AdoptionApplication::query()
        ->where('adoption_case_id', $case->id)
        ->where('applicant_user_id', $applicant->id)
        ->exists())->toBeTrue();
});

test('closing a case preserves append only application and case history', function () {
    [$owner, $applicant, $case] = adoptionActorsAndCase();
    $application = AdoptionApplication::factory()->create([
        'adoption_case_id' => $case->id,
        'applicant_user_id' => $applicant->id,
        'status' => AdoptionApplicationStatus::Screening,
    ]);

    $closed = app(CloseAdoptionCase::class)->handle($owner, $case, 1);
    $events = AdoptionEvent::query()->where('adoption_case_id', $case->id)->get();

    expect($closed->status)->toBe(AdoptionCaseStatus::Closed)
        ->and($closed->closed_at)->not->toBeNull()
        ->and($application->refresh()->status)->toBe(AdoptionApplicationStatus::Closed)
        ->and($events->pluck('event_type')->all())->toContain(
            'application-closed-with-case',
            'case-closed',
        )
        ->and(fn () => $events->last()?->update(['event_type' => 'rewritten']))
        ->toThrow(LogicException::class);
});

test('adoption cases may link taxonomy and domestic classifications without name based identifiers', function () {
    $taxon = Taxon::factory()->create();
    $classification = DomesticClassification::factory()->create(['taxon_id' => $taxon->id]);
    $case = AdoptionCase::factory()->create([
        'taxon_id' => $taxon->id,
        'domestic_classification_id' => $classification->id,
    ]);

    expect($case->taxon()->value('taxa.id'))->toBe($taxon->id)
        ->and($case->domesticClassification()->value('domestic_classifications.id'))
        ->toBe($classification->id);
});

/**
 * @return array{User, User, AdoptionCase}
 */
function adoptionActorsAndCase(): array
{
    $owner = User::factory()->create();
    $applicant = User::factory()->create();
    $listing = Listing::factory()->adoption()->create([
        'owner_id' => $owner->id,
        'owner_key' => $owner->actor_key,
        'owner_name' => $owner->name,
    ]);
    $case = AdoptionCase::factory()->create([
        'listing_id' => $listing->id,
        'status' => AdoptionCaseStatus::Published,
        'lock_version' => 1,
    ]);

    return [$owner, $applicant, $case];
}

/** @param array<string, string> $overrides */
function adoptionApplicationData(array $overrides = []): AdoptionApplicationData
{
    return new AdoptionApplicationData(
        placementType: AdoptionPlacementType::Adoption,
        message: 'We would like to follow the complete welfare-focused placement process.',
        privateProfile: [
            'experience' => $overrides['experience'] ?? 'Several years of adult animal care.',
            'home_context' => $overrides['home_context'] ?? 'Secure indoor home with permission.',
            'household' => $overrides['household'] ?? 'Two consenting adults.',
            'other_animals' => $overrides['other_animals'] ?? 'No resident animals.',
            'care_plan' => $overrides['care_plan'] ?? 'Daily care and emergency budget.',
            'placement_reason' => $overrides['placement_reason'] ?? 'Compatible household routine.',
            'transport_plan' => $overrides['transport_plan'] ?? 'Secure carrier and local meeting.',
        ],
        termsAccepted: true,
        privacyAccepted: true,
        referenceContactConsent: false,
    );
}
