<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceManagementClaimAction;
use App\Models\Place;
use App\Models\PlaceManagementClaim;
use App\Models\PlaceManagementClaimEvent;
use App\Models\PlaceManagementClaimEvidence;
use App\Models\User;
use App\Services\PlaceManagementFingerprint;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;
use Throwable;

final readonly class StorePlaceManagementClaimEvidence
{
    public function __construct(
        private Gate $gate,
        private PlaceManagementFingerprint $fingerprints,
    ) {}

    public function handle(
        User $actor,
        PlaceManagementClaim $claim,
        UploadedFile $upload,
        string $evidenceType,
        ?CarbonImmutable $issuedAt,
        ?CarbonImmutable $expiresAt,
        string $idempotencyKey,
    ): PlaceManagementClaimEvidence {
        $this->gate->forUser($actor)->authorize('addEvidence', $claim);
        validator([
            'upload' => $upload,
            'evidence_type' => $evidenceType,
            'issued_at' => $issuedAt,
            'expires_at' => $expiresAt,
            'idempotency_key' => $idempotencyKey,
        ], [
            'upload' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp', 'mimetypes:application/pdf,image/jpeg,image/png,image/webp'],
            'evidence_type' => ['required', 'string', 'in:organization_document,domain_control,phone_control,postal_control,other'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:issued_at'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();

        $realPath = $upload->getRealPath();
        $checksum = is_string($realPath) ? hash_file('sha256', $realPath) : false;
        $mime = $upload->getMimeType();
        if (! is_string($checksum) || ! is_string($mime)) {
            throw ValidationException::withMessages([
                'upload' => __('places.management.validation.evidence_unreadable'),
            ]);
        }

        $fingerprint = $this->fingerprints->make([
            'actor_id' => $actor->id,
            'claim_id' => $claim->id,
            'checksum' => $checksum,
            'evidence_type' => $evidenceType,
            'issued_at' => $issuedAt?->toIso8601String(),
            'expires_at' => $expiresAt?->toIso8601String(),
        ]);
        $existing = $this->existingReplay($claim, $actor, $idempotencyKey, $fingerprint);
        if ($existing instanceof PlaceManagementClaimEvidence) {
            return $existing;
        }

        $stableKey = 'place-evidence-'.Str::lower((string) Str::ulid());
        $extension = match ($mime) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw ValidationException::withMessages([
                'upload' => __('places.management.validation.evidence_type'),
            ]),
        };
        $directory = 'place-management-claims/'.$claim->stable_key;
        $path = Storage::disk('local')->putFileAs($directory, $upload, $stableKey.'.'.$extension);
        if (! is_string($path)) {
            throw ValidationException::withMessages([
                'upload' => __('places.management.validation.evidence_storage'),
            ]);
        }

        $wasReplay = false;
        try {
            $evidence = DB::transaction(function () use (
                $actor,
                $checksum,
                $claim,
                $evidenceType,
                $expiresAt,
                $fingerprint,
                $idempotencyKey,
                $issuedAt,
                $mime,
                $path,
                $upload,
                &$wasReplay,
            ): PlaceManagementClaimEvidence {
                Place::query()->lockForUpdate()->findOrFail($claim->place_id);
                $lockedClaim = PlaceManagementClaim::query()->lockForUpdate()->findOrFail($claim->id);
                $this->gate->forUser($actor)->authorize('addEvidence', $lockedClaim);
                $existing = $this->existingReplay($lockedClaim, $actor, $idempotencyKey, $fingerprint);
                if ($existing instanceof PlaceManagementClaimEvidence) {
                    $wasReplay = true;

                    return $existing;
                }

                if ($lockedClaim->evidence()->count() >= 8) {
                    throw ValidationException::withMessages([
                        'upload' => __('places.management.validation.evidence_limit'),
                    ]);
                }

                $evidence = $lockedClaim->evidence()->create([
                    'uploaded_by_user_id' => $actor->id,
                    'stable_key' => pathinfo($path, PATHINFO_FILENAME),
                    'private_disk' => 'local',
                    'private_path' => $path,
                    'original_name' => Str::limit(basename(str_replace('\\', '/', $upload->getClientOriginalName())), 255, ''),
                    'mime_type' => $mime,
                    'byte_size' => $upload->getSize(),
                    'checksum_sha256' => $checksum,
                    'evidence_type' => $evidenceType,
                    'issued_at' => $issuedAt,
                    'expires_at' => $expiresAt,
                    'upload_idempotency_key' => $idempotencyKey,
                    'upload_payload_fingerprint' => $fingerprint,
                    'created_at' => now(),
                ]);
                $newVersion = $lockedClaim->lock_version + 1;
                $lockedClaim->forceFill([
                    'evidence_expires_at' => $this->minimumEvidenceExpiry($lockedClaim, $expiresAt),
                    'lock_version' => $newVersion,
                ])->save();
                PlaceManagementClaimEvent::query()->create([
                    'place_management_claim_id' => $lockedClaim->id,
                    'actor_user_id' => $actor->id,
                    'action' => PlaceManagementClaimAction::EvidenceUploaded,
                    'from_status' => $lockedClaim->status,
                    'to_status' => $lockedClaim->status,
                    'reason_code' => 'evidence-uploaded',
                    'idempotency_key' => Uuid::uuid5(Uuid::NAMESPACE_URL, 'place-claim-evidence:'.$idempotencyKey)->toString(),
                    'payload_fingerprint' => $fingerprint,
                    'audit_context' => ['channel' => 'application', 'evidence_key' => $evidence->stable_key],
                    'expected_lock_version' => $newVersion - 1,
                    'result_lock_version' => $newVersion,
                    'created_at' => now(),
                ]);

                return $evidence;
            }, 3);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        if ($wasReplay) {
            Storage::disk('local')->delete($path);
        }

        return $evidence;
    }

    private function existingReplay(
        PlaceManagementClaim $claim,
        User $actor,
        string $idempotencyKey,
        string $fingerprint,
    ): ?PlaceManagementClaimEvidence {
        $existing = PlaceManagementClaimEvidence::query()
            ->where('place_management_claim_id', $claim->id)
            ->where('upload_idempotency_key', $idempotencyKey)
            ->first();
        if (! $existing instanceof PlaceManagementClaimEvidence) {
            return null;
        }

        if ($existing->uploaded_by_user_id !== $actor->id
            || ! hash_equals($existing->upload_payload_fingerprint, $fingerprint)) {
            throw ValidationException::withMessages([
                'idempotency_key' => __('places.management.validation.evidence_idempotency_conflict'),
            ]);
        }

        return $existing;
    }

    private function minimumEvidenceExpiry(
        PlaceManagementClaim $claim,
        ?CarbonImmutable $newExpiry,
    ): ?CarbonImmutable {
        $current = $claim->evidence()->whereNotNull('expires_at')->min('expires_at');
        if (is_string($current)) {
            $current = CarbonImmutable::parse($current);
        }

        if ($current instanceof CarbonImmutable && $newExpiry instanceof CarbonImmutable) {
            return $current->lessThan($newExpiry) ? $current : $newExpiry;
        }

        return $current instanceof CarbonImmutable ? $current : $newExpiry;
    }
}
