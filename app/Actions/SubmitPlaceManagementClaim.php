<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OrganizationRole;
use App\Enums\PlaceManagementClaimAction;
use App\Enums\PlaceManagementClaimPurpose;
use App\Enums\PlaceManagementClaimStatus;
use App\Enums\PlaceManagementRole;
use App\Enums\PlaceManagementScope;
use App\Enums\PlaceVerificationMethod;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Place;
use App\Models\PlaceManagementClaim;
use App\Models\PlaceManagementClaimEvent;
use App\Models\User;
use App\Services\PlaceManagementFingerprint;
use App\Services\PlaceManagementNotifier;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;

final readonly class SubmitPlaceManagementClaim
{
    public function __construct(
        private Gate $gate,
        private PlaceManagementFingerprint $fingerprints,
        private PlaceManagementNotifier $notifications,
    ) {}

    /** @param list<PlaceManagementScope> $scopes */
    public function handle(
        User $actor,
        Place $place,
        ?Organization $representedOrganization,
        PlaceManagementRole $role,
        array $scopes,
        PlaceVerificationMethod $method,
        ?string $contactDetails,
        string $idempotencyKey,
        PlaceManagementClaimPurpose $purpose = PlaceManagementClaimPurpose::Initial,
        ?PlaceManagementClaim $predecessor = null,
        ?User $targetUser = null,
    ): PlaceManagementClaim {
        $this->gate->forUser($actor)->authorize('submitManagementClaim', $place);
        $validated = validator([
            'idempotency_key' => $idempotencyKey,
            'contact_details' => $contactDetails === null ? null : trim($contactDetails),
            'scopes' => array_map(static fn (mixed $scope): mixed => $scope instanceof PlaceManagementScope ? $scope->value : $scope, $scopes),
        ], [
            'idempotency_key' => ['required', 'uuid'],
            'contact_details' => ['nullable', 'string', 'max:500'],
            'scopes' => ['required', 'array', 'min:1', 'max:12'],
            'scopes.*' => ['required', 'distinct', 'in:'.implode(',', array_column(PlaceManagementScope::cases(), 'value'))],
        ])->validate();

        $scopeValues = $validated['scopes'];
        sort($scopeValues);
        $payloadFingerprint = $this->fingerprints->make([
            'actor_id' => $actor->id,
            'place_id' => $place->id,
            'organization_id' => $representedOrganization?->id,
            'role' => $role->value,
            'scopes' => $scopeValues,
            'method' => $method->value,
            'contact_details' => $validated['contact_details'],
            'purpose' => $purpose->value,
            'predecessor_id' => $predecessor?->id,
            'target_user_id' => $targetUser?->id,
        ]);

        $existing = PlaceManagementClaim::query()
            ->with('requestedScopes')
            ->where('submission_idempotency_key', $validated['idempotency_key'])
            ->first();
        if ($existing instanceof PlaceManagementClaim) {
            return $this->validatedReplay($existing, $actor, $place, $payloadFingerprint);
        }

        return DB::transaction(function () use (
            $actor,
            $method,
            $payloadFingerprint,
            $place,
            $predecessor,
            $purpose,
            $representedOrganization,
            $role,
            $scopeValues,
            $targetUser,
            $validated,
        ): PlaceManagementClaim {
            $lockedPlace = Place::query()->lockForUpdate()->findOrFail($place->id);
            $this->gate->forUser($actor)->authorize('submitManagementClaim', $lockedPlace);
            $organization = $this->authorizedOrganization($actor, $representedOrganization);

            $existing = PlaceManagementClaim::query()
                ->with('requestedScopes')
                ->where('submission_idempotency_key', $validated['idempotency_key'])
                ->first();
            if ($existing instanceof PlaceManagementClaim) {
                return $this->validatedReplay($existing, $actor, $lockedPlace, $payloadFingerprint);
            }

            $conflictKey = $this->conflictKey($lockedPlace, $actor, $organization, $role);
            if (PlaceManagementClaim::query()->where('active_conflict_key', $conflictKey)->exists()) {
                throw ValidationException::withMessages([
                    'management_claim' => __('places.management.validation.active_claim_conflict'),
                ]);
            }

            if ($predecessor !== null && $predecessor->place_id !== $lockedPlace->id) {
                throw ValidationException::withMessages([
                    'predecessor_claim' => __('places.management.validation.predecessor_mismatch'),
                ]);
            }

            $claim = PlaceManagementClaim::query()->create([
                'stable_key' => 'place-claim-'.Str::lower((string) Str::ulid()),
                'place_id' => $lockedPlace->id,
                'claimant_user_id' => $actor->id,
                'represented_organization_id' => $organization?->id,
                'predecessor_claim_id' => $predecessor?->id,
                'target_user_id' => $targetUser?->id,
                'claim_purpose' => $purpose,
                'requested_role' => $role,
                'verification_method' => $method,
                'contact_details' => $validated['contact_details'],
                'status' => PlaceManagementClaimStatus::Pending,
                'submitted_at' => now(),
                'expires_at' => now()->addDays(30),
                'active_conflict_key' => $conflictKey,
                'submission_idempotency_key' => $validated['idempotency_key'],
                'submission_payload_fingerprint' => $payloadFingerprint,
                'lock_version' => 0,
            ]);

            $claim->requestedScopes()->createMany(array_map(
                static fn (string $scope): array => ['scope' => $scope, 'created_at' => now()],
                $scopeValues,
            ));

            $event = PlaceManagementClaimEvent::query()->create([
                'place_management_claim_id' => $claim->id,
                'actor_user_id' => $actor->id,
                'action' => PlaceManagementClaimAction::Submitted,
                'from_status' => null,
                'to_status' => PlaceManagementClaimStatus::Pending,
                'reason_code' => 'claim-submitted',
                'idempotency_key' => Uuid::uuid5(Uuid::NAMESPACE_URL, 'place-claim-submit:'.$validated['idempotency_key'])->toString(),
                'payload_fingerprint' => $payloadFingerprint,
                'audit_context' => ['channel' => 'application'],
                'expected_lock_version' => null,
                'result_lock_version' => 0,
                'created_at' => now(),
            ]);
            $this->notifications->record(
                event: $event,
                recipients: [$actor],
                kind: 'claim-submitted',
                messageKey: 'places.management.notifications.claim_submitted',
                safePayload: [
                    'claim_key' => $claim->stable_key,
                    'place_key' => $lockedPlace->stable_key,
                    'status' => PlaceManagementClaimStatus::Pending->value,
                ],
            );

            return $claim->load('requestedScopes');
        }, 3);
    }

    private function authorizedOrganization(User $actor, ?Organization $organization): ?Organization
    {
        if (! $organization instanceof Organization) {
            return null;
        }

        $canonical = Organization::query()->active()->lockForUpdate()->find($organization->id);
        $authorized = $canonical instanceof Organization
            && OrganizationMembership::query()
                ->active()
                ->where('organization_id', $canonical->id)
                ->where('user_id', $actor->id)
                ->whereIn('role', OrganizationRole::placeManagerValues())
                ->exists();

        if (! $authorized) {
            throw new AuthorizationException(__('places.management.validation.organization_unavailable'));
        }

        return $canonical;
    }

    private function conflictKey(
        Place $place,
        User $actor,
        ?Organization $organization,
        PlaceManagementRole $role,
    ): string {
        return hash('sha256', implode('|', [
            (string) $place->id,
            (string) $actor->id,
            (string) ($organization?->id ?? 'personal'),
            $role->value,
        ]));
    }

    private function validatedReplay(
        PlaceManagementClaim $claim,
        User $actor,
        Place $place,
        string $fingerprint,
    ): PlaceManagementClaim {
        if ($claim->claimant_user_id !== $actor->id
            || $claim->place_id !== $place->id
            || ! hash_equals($claim->submission_payload_fingerprint, $fingerprint)) {
            throw ValidationException::withMessages([
                'idempotency_key' => __('places.management.validation.idempotency_conflict'),
            ]);
        }

        return $claim;
    }
}
