<?php

declare(strict_types=1);

use App\Actions\ApprovePlaceManagementClaim;
use App\Actions\RecusePlaceManagementClaimReviewer;
use App\Actions\RejectPlaceManagementClaim;
use App\Actions\RequestPlaceManagementClaimInformation;
use App\Actions\ResubmitPlaceManagementClaimInformation;
use App\Actions\RevokePlaceManagementClaim;
use App\Actions\StartPlaceManagementClaimReview;
use App\Actions\StorePlaceManagementClaimEvidence;
use App\Actions\SubmitPlaceManagementClaim;
use App\Enums\PlaceManagementClaimStatus;
use App\Enums\PlaceManagementRole;
use App\Enums\PlaceManagementReviewerRole;
use App\Enums\PlaceManagementScope;
use App\Enums\PlaceManagerAuthorityStatus;
use App\Enums\PlaceVerificationMethod;
use App\Models\Place;
use App\Models\PlaceManagementClaim;
use App\Models\PlaceManagementClaimEvent;
use App\Models\PlaceManagementReviewer;
use App\Models\PlaceManagerAuthority;
use App\Models\User;
use App\Notifications\PlaceManagementTransitionNotification;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('local');
    Notification::fake();
});

test('focused actions enforce the information request loop and permanent reviewer recusal', function () {
    [$claimant, $place, $claim] = claimWithEvidence();
    $reviewer = appointedReviewer();

    $reviewing = app(StartPlaceManagementClaimReview::class)->handle(
        $reviewer,
        $claim,
        '6bc0e7e2-17ad-43c6-aecf-3b16f95205aa',
        1,
    );
    $needsInformation = app(RequestPlaceManagementClaimInformation::class)->handle(
        $reviewer,
        $reviewing,
        '107f102a-3e57-44c1-b710-94625176ec57',
        2,
        'additional-proof-required',
        'Please provide a current registration document.',
    );
    $resubmitted = app(ResubmitPlaceManagementClaimInformation::class)->handle(
        $claimant,
        $needsInformation,
        'bc7820ac-74dc-421b-a806-d5302304f8a3',
        3,
        'Updated documentation uploaded.',
    );

    expect($reviewing->status)->toBe(PlaceManagementClaimStatus::UnderReview)
        ->and($needsInformation->status)->toBe(PlaceManagementClaimStatus::NeedsInformation)
        ->and($resubmitted->status)->toBe(PlaceManagementClaimStatus::Pending)
        ->and($resubmitted->lock_version)->toBe(4)
        ->and($resubmitted->events()->count())->toBe(5);

    $reviewingAgain = app(StartPlaceManagementClaimReview::class)->handle(
        $reviewer,
        $resubmitted,
        'aed414ab-4c64-4ccb-a322-a896ce12e5a1',
        4,
    );
    $recused = app(RecusePlaceManagementClaimReviewer::class)->handle(
        $reviewer,
        $reviewingAgain,
        '3117676a-67be-4fbe-8abe-d7cc657703df',
        5,
        'personal-conflict',
        'Known professional relationship.',
    );

    expect($recused->status)->toBe(PlaceManagementClaimStatus::Pending)
        ->and($recused->reviewer_user_id)->toBeNull()
        ->and($recused->reviewerRecusals()->where('reviewer_user_id', $reviewer->id)->count())->toBe(1)
        ->and(fn () => app(StartPlaceManagementClaimReview::class)->handle(
            $reviewer,
            $recused,
            'cd3c4f00-4663-40ee-bf07-daf4237d5fd2',
            6,
        ))->toThrow(AuthorizationException::class);
});

test('approval creates one exact scoped authority and repeated operation is idempotent', function () {
    [, $place, $claim] = claimWithEvidence();
    $reviewer = appointedReviewer();
    $reviewing = app(StartPlaceManagementClaimReview::class)->handle(
        $reviewer,
        $claim,
        '1713a92f-f807-483e-bc9d-55be2de542fd',
        1,
    );

    $approved = app(ApprovePlaceManagementClaim::class)->handle(
        actor: $reviewer,
        claim: $reviewing,
        approvedScopes: [PlaceManagementScope::Hours, PlaceManagementScope::OfficialResponses],
        authorityExpiresAt: now()->addMonths(3)->toImmutable(),
        idempotencyKey: '1795692d-3c06-4f69-9f76-33fd9bf38650',
        expectedVersion: 2,
        reasonCode: 'evidence-confirmed',
        decisionDetail: 'Registration and domain control confirmed.',
    );
    $replay = app(ApprovePlaceManagementClaim::class)->handle(
        actor: $reviewer,
        claim: $approved,
        approvedScopes: [PlaceManagementScope::OfficialResponses, PlaceManagementScope::Hours],
        authorityExpiresAt: now()->addMonths(3)->toImmutable(),
        idempotencyKey: '1795692d-3c06-4f69-9f76-33fd9bf38650',
        expectedVersion: 2,
        reasonCode: 'evidence-confirmed',
        decisionDetail: 'Registration and domain control confirmed.',
    );
    $authority = $approved->authority()->with('scopes')->sole();

    expect($approved->status)->toBe(PlaceManagementClaimStatus::Approved)
        ->and($replay->is($approved))->toBeTrue()
        ->and($authority->status)->toBe(PlaceManagerAuthorityStatus::Active)
        ->and($authority->place_id)->toBe($place->id)
        ->and($authority->granted_to_user_id)->toBe($claim->claimant_user_id)
        ->and($authority->scopes->pluck('scope')->map->value->sort()->values()->all())->toBe([
            PlaceManagementScope::Hours->value,
            PlaceManagementScope::OfficialResponses->value,
        ])
        ->and(PlaceManagerAuthority::query()->count())->toBe(1)
        ->and($approved->events()->where('action', 'approved')->count())->toBe(1);
});

test('expired evidence reviewer conflicts and wrong account decisions fail without mutation', function () {
    [$claimant, $place, $claim] = claimWithEvidence(now()->subDay()->toImmutable());
    $reviewer = appointedReviewer();
    $reviewing = app(StartPlaceManagementClaimReview::class)->handle(
        $reviewer,
        $claim,
        '21497ac2-54e9-40b0-8314-24f7f98b43c3',
        1,
    );

    expect(fn () => app(ApprovePlaceManagementClaim::class)->handle(
        $reviewer,
        $reviewing,
        [PlaceManagementScope::Hours],
        now()->addMonth()->toImmutable(),
        '52a857c4-8c85-4dc7-8bc9-e1d2b9883f32',
        2,
        'evidence-confirmed',
        null,
    ))->toThrow(ValidationException::class)
        ->and(fn () => app(RejectPlaceManagementClaim::class)->handle(
            $claimant,
            $reviewing,
            'a0bc34ce-8c61-49f4-85fc-1bd87a184ec9',
            2,
            'not-eligible',
            null,
        ))->toThrow(AuthorizationException::class)
        ->and($reviewing->fresh()->status)->toBe(PlaceManagementClaimStatus::UnderReview)
        ->and(PlaceManagerAuthority::query()->count())->toBe(0);

    $ownerReviewer = appointedReviewer();
    $place->forceFill(['owner_user_id' => $ownerReviewer->id])->save();
    $otherClaim = submitTransitionClaim(
        User::factory()->create(),
        $place,
        '3596dad4-9fe5-42c5-ae4a-1df7fe7ce835',
    );
    expect(fn () => app(StartPlaceManagementClaimReview::class)->handle(
        $ownerReviewer,
        $otherClaim,
        'e5425bfa-21f4-49de-b2e7-c00156cc856a',
        0,
    ))->toThrow(AuthorizationException::class);
});

test('revocation atomically ends authority and cannot emit duplicate notifications', function () {
    [$claimant, , $claim] = claimWithEvidence();
    $reviewer = appointedReviewer();
    $reviewing = app(StartPlaceManagementClaimReview::class)->handle(
        $reviewer,
        $claim,
        '9f68d748-ce34-47a7-9332-57a98af39448',
        1,
    );
    $approved = app(ApprovePlaceManagementClaim::class)->handle(
        $reviewer,
        $reviewing,
        [PlaceManagementScope::Hours],
        now()->addMonth()->toImmutable(),
        '9350880a-f76a-4637-9c69-7c19e6afff80',
        2,
        'evidence-confirmed',
        null,
    );
    $revoked = app(RevokePlaceManagementClaim::class)->handle(
        $reviewer,
        $approved,
        '2f6dc42d-e103-42fa-97dc-fea32a864535',
        3,
        'verified-control-lost',
        'Published control can no longer be confirmed.',
    );
    $replay = app(RevokePlaceManagementClaim::class)->handle(
        $reviewer,
        $revoked,
        '2f6dc42d-e103-42fa-97dc-fea32a864535',
        3,
        'verified-control-lost',
        'Published control can no longer be confirmed.',
    );

    expect($revoked->status)->toBe(PlaceManagementClaimStatus::Revoked)
        ->and($replay->is($revoked))->toBeTrue()
        ->and($revoked->authority()->sole()->status)->toBe(PlaceManagerAuthorityStatus::Revoked)
        ->and($revoked->authority()->sole()->active_authority_key)->toBeNull()
        ->and($revoked->events()->where('action', 'revoked')->count())->toBe(1);

    Notification::assertSentToTimes($claimant, PlaceManagementTransitionNotification::class, 4);
});

/** @return array{User, Place, PlaceManagementClaim} */
function claimWithEvidence(?CarbonImmutable $evidenceExpiry = null): array
{
    $claimant = User::factory()->create();
    $place = Place::factory()->public()->create();
    $claim = submitTransitionClaim(
        $claimant,
        $place,
        '030bfae8-8321-4b48-995d-9ada7df69310',
    );
    app(StorePlaceManagementClaimEvidence::class)->handle(
        $claimant,
        $claim,
        UploadedFile::fake()->createWithContent(
            'proof.pdf',
            "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF",
        ),
        'organization_document',
        now()->subMonth()->toImmutable(),
        $evidenceExpiry ?? now()->addMonths(6)->toImmutable(),
        '16f47436-8d44-4139-9373-935dc5d2fef5',
    );

    return [$claimant, $place, $claim->fresh()];
}

function appointedReviewer(
    PlaceManagementReviewerRole $role = PlaceManagementReviewerRole::Reviewer,
): User {
    $reviewer = User::factory()->administrator()->create();
    PlaceManagementReviewer::factory()->for($reviewer)->create(['role' => $role]);

    return $reviewer;
}

function submitTransitionClaim(User $actor, Place $place, string $idempotencyKey): PlaceManagementClaim
{
    return app(SubmitPlaceManagementClaim::class)->handle(
        actor: $actor,
        place: $place,
        representedOrganization: null,
        role: PlaceManagementRole::Owner,
        scopes: [PlaceManagementScope::Hours, PlaceManagementScope::OfficialResponses],
        method: PlaceVerificationMethod::OrganizationDocument,
        contactDetails: 'claims@example.test',
        idempotencyKey: $idempotencyKey,
    );
}
