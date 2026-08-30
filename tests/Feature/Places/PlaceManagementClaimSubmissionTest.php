<?php

declare(strict_types=1);

use App\Actions\StorePlaceManagementClaimEvidence;
use App\Actions\SubmitPlaceManagementClaim;
use App\Enums\OrganizationRole;
use App\Enums\PlaceManagementClaimStatus;
use App\Enums\PlaceManagementRole;
use App\Enums\PlaceManagementScope;
use App\Enums\PlaceVerificationMethod;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Place;
use App\Models\PlaceManagementClaim;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

test('an eligible verified user submits one idempotent relational claim with exact requested scopes', function () {
    $claimant = User::factory()->create();
    $place = Place::factory()->public()->create();
    $key = 'a5829d9a-6537-42ec-b03f-5acdc6401bc2';

    $claim = app(SubmitPlaceManagementClaim::class)->handle(
        actor: $claimant,
        place: $place,
        representedOrganization: null,
        role: PlaceManagementRole::Owner,
        scopes: [PlaceManagementScope::Hours, PlaceManagementScope::OfficialResponses],
        method: PlaceVerificationMethod::OrganizationDocument,
        contactDetails: 'claims@example.test',
        idempotencyKey: $key,
    );
    $replay = app(SubmitPlaceManagementClaim::class)->handle(
        actor: $claimant,
        place: $place,
        representedOrganization: null,
        role: PlaceManagementRole::Owner,
        scopes: [PlaceManagementScope::OfficialResponses, PlaceManagementScope::Hours],
        method: PlaceVerificationMethod::OrganizationDocument,
        contactDetails: 'claims@example.test',
        idempotencyKey: $key,
    );
    $raw = DB::table('place_management_claims')->where('id', $claim->id)->firstOrFail();

    expect($claim->status)->toBe(PlaceManagementClaimStatus::Pending)
        ->and($replay->is($claim))->toBeTrue()
        ->and(PlaceManagementClaim::query()->count())->toBe(1)
        ->and($claim->requestedScopes()->pluck('scope')->map->value->sort()->values()->all())->toBe([
            PlaceManagementScope::Hours->value,
            PlaceManagementScope::OfficialResponses->value,
        ])
        ->and($claim->events()->count())->toBe(1)
        ->and((string) $raw->contact_details)->not->toContain('claims@example.test');
});

test('submission fails closed for blocked inactive or unverified accounts and payload changing replays', function (string $state) {
    $factory = User::factory();
    $actor = match ($state) {
        'blocked' => $factory->blocked()->create(),
        'inactive' => $factory->suspended()->create(),
        'unverified' => $factory->unverified()->create(),
    };
    $place = Place::factory()->public()->create();

    expect(fn () => submitManagementClaim($actor, $place, '53fc7f8b-311c-46e9-a74f-2d858950312f'))
        ->toThrow(AuthorizationException::class)
        ->and(PlaceManagementClaim::query()->count())->toBe(0);
})->with(['blocked', 'inactive', 'unverified']);

test('represented organizations are canonical and require a current authorized membership', function () {
    $claimant = User::factory()->create();
    $other = User::factory()->create();
    $place = Place::factory()->public()->create();
    $allowed = Organization::factory()->verified()->create();
    $foreign = Organization::factory()->verified()->create();
    OrganizationMembership::factory()->for($allowed)->for($claimant)->active()->create([
        'role' => OrganizationRole::Administrator,
    ]);
    OrganizationMembership::factory()->for($foreign)->for($other)->active()->create([
        'role' => OrganizationRole::Administrator,
    ]);

    $claim = app(SubmitPlaceManagementClaim::class)->handle(
        actor: $claimant,
        place: $place,
        representedOrganization: $allowed,
        role: PlaceManagementRole::OrganizationManager,
        scopes: [PlaceManagementScope::PublicInformation],
        method: PlaceVerificationMethod::DomainEmail,
        contactDetails: 'manager@example.test',
        idempotencyKey: '6d775b41-3fe8-4c70-b649-350855fbf286',
    );

    expect($claim->represented_organization_id)->toBe($allowed->id)
        ->and(fn () => app(SubmitPlaceManagementClaim::class)->handle(
            actor: $claimant,
            place: $place,
            representedOrganization: $foreign,
            role: PlaceManagementRole::OrganizationManager,
            scopes: [PlaceManagementScope::PublicInformation],
            method: PlaceVerificationMethod::DomainEmail,
            contactDetails: 'manager@example.test',
            idempotencyKey: '5f014740-3568-44d4-ad9d-321844d1680a',
        ))->toThrow(AuthorizationException::class);
});

test('active claim conflicts and changed idempotent payloads are rejected without duplicate history', function () {
    $claimant = User::factory()->create();
    $place = Place::factory()->public()->create();
    $key = 'ae85cbb1-6905-49e4-b48e-5054410bd20f';
    submitManagementClaim($claimant, $place, $key);

    expect(fn () => app(SubmitPlaceManagementClaim::class)->handle(
        actor: $claimant,
        place: $place,
        representedOrganization: null,
        role: PlaceManagementRole::Owner,
        scopes: [PlaceManagementScope::Safety],
        method: PlaceVerificationMethod::OrganizationDocument,
        contactDetails: 'changed@example.test',
        idempotencyKey: $key,
    ))->toThrow(ValidationException::class)
        ->and(fn () => submitManagementClaim(
            $claimant,
            $place,
            'e271575f-bcea-491a-961d-4ff75cff4656',
        ))->toThrow(ValidationException::class)
        ->and(PlaceManagementClaim::query()->count())->toBe(1);
});

test('verification evidence is content checked stored privately and upload replay is idempotent', function () {
    Storage::fake('local');
    $claimant = User::factory()->create();
    $place = Place::factory()->public()->create();
    $claim = submitManagementClaim($claimant, $place, '6fa76fc0-770b-4a4a-82d0-dbaafc5779f6');
    $upload = UploadedFile::fake()->createWithContent(
        'registration.pdf',
        "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF",
    );

    $evidence = app(StorePlaceManagementClaimEvidence::class)->handle(
        actor: $claimant,
        claim: $claim,
        upload: $upload,
        evidenceType: 'organization_document',
        issuedAt: now()->subMonth()->toImmutable(),
        expiresAt: now()->addMonths(6)->toImmutable(),
        idempotencyKey: 'cb1651a6-c972-4475-84c2-52b78068fd8e',
    );
    $replay = app(StorePlaceManagementClaimEvidence::class)->handle(
        actor: $claimant,
        claim: $claim,
        upload: $upload,
        evidenceType: 'organization_document',
        issuedAt: now()->subMonth()->toImmutable(),
        expiresAt: now()->addMonths(6)->toImmutable(),
        idempotencyKey: 'cb1651a6-c972-4475-84c2-52b78068fd8e',
    );

    Storage::disk('local')->assertExists($evidence->private_path);
    expect($evidence->private_disk)->toBe('local')
        ->and($evidence->private_path)->toStartWith("place-management-claims/{$claim->stable_key}/")
        ->and($replay->is($evidence))->toBeTrue()
        ->and($claim->evidence()->count())->toBe(1)
        ->and($evidence->toArray())->not->toHaveKeys([
            'private_disk',
            'private_path',
            'original_name',
            'checksum_sha256',
            'upload_idempotency_key',
            'upload_payload_fingerprint',
        ]);
});

function submitManagementClaim(User $actor, Place $place, string $idempotencyKey): PlaceManagementClaim
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
