<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceAccessGrantStatus;
use App\Models\Place;
use App\Models\PlaceAccessAudit;
use App\Models\PlaceAccessGrant;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class RevokePlaceAccess
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        Place $place,
        PlaceAccessGrant $grant,
        string $reasonCode,
        string $idempotencyKey,
    ): PlaceAccessGrant {
        $this->gate->forUser($actor)->authorize('manageAccess', $place);
        Validator::make(compact('reasonCode', 'idempotencyKey'), [
            'reasonCode' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $operationKey = hash('sha256', "place-grant-revoke|{$grant->id}|{$actor->id}|{$idempotencyKey}");

        return DB::transaction(function () use ($actor, $place, $grant, $reasonCode, $operationKey): PlaceAccessGrant {
            $lockedPlace = Place::query()->lockForUpdate()->findOrFail($place->id);
            $lockedGrant = PlaceAccessGrant::query()->lockForUpdate()->findOrFail($grant->id);
            $this->gate->forUser($actor)->authorize('manageAccess', $lockedPlace);

            if ($lockedGrant->place_id !== $lockedPlace->id) {
                throw ValidationException::withMessages([
                    'grant' => __('places.validation.grant_place_mismatch'),
                ]);
            }

            if ($lockedGrant->revocation_idempotency_key !== null) {
                if ($lockedGrant->revocation_idempotency_key !== $operationKey
                    || $lockedGrant->revocation_reason_code !== $reasonCode
                ) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('places.validation.idempotency_conflict'),
                    ]);
                }

                return $lockedGrant;
            }

            $lockedGrant->forceFill([
                'status' => PlaceAccessGrantStatus::Revoked,
                'revoked_by_user_id' => $actor->id,
                'revoked_at' => now(),
                'revocation_reason_code' => $reasonCode,
                'revocation_idempotency_key' => $operationKey,
            ])->save();

            PlaceAccessAudit::query()->create([
                'place_id' => $lockedPlace->id,
                'user_id' => $actor->id,
                'place_access_grant_id' => $lockedGrant->id,
                'event_id' => $lockedGrant->event_id,
                'event_type' => 'exact-location-access-revoked',
                'purpose' => $lockedGrant->purpose->value,
                'channel' => 'place-access-management',
                'metadata' => ['reason_code' => $reasonCode],
                'created_at' => now(),
            ]);

            return $lockedGrant;
        }, 3);
    }
}
