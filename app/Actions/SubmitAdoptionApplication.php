<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\AdoptionApplicationData;
use App\Models\AdoptionApplication;
use App\Models\AdoptionCase;
use App\Models\AdoptionEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SubmitAdoptionApplication
{
    public function __construct(private readonly Gate $gate) {}

    public function handle(
        User $applicant,
        AdoptionCase $case,
        AdoptionApplicationData $data,
        string $idempotencyKey,
    ): AdoptionApplication {
        $this->gate->forUser($applicant)->authorize('apply', $case);

        return DB::transaction(function () use (
            $applicant,
            $case,
            $data,
            $idempotencyKey,
        ): AdoptionApplication {
            $existingByKey = AdoptionApplication::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingByKey !== null) {
                if (
                    $existingByKey->adoption_case_id !== $case->id
                    || $existingByKey->applicant_user_id !== $applicant->id
                ) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('adoption.validation.idempotency_conflict'),
                    ]);
                }

                return $existingByKey;
            }

            $lockedCase = AdoptionCase::query()
                ->with('listing:id,owner_key,status,moderation_status')
                ->lockForUpdate()
                ->findOrFail($case->id);
            $this->gate->forUser($applicant)->authorize('apply', $lockedCase);

            $existingApplication = AdoptionApplication::query()
                ->where('adoption_case_id', $lockedCase->id)
                ->where('applicant_user_id', $applicant->id)
                ->first();

            if ($existingApplication !== null) {
                throw ValidationException::withMessages([
                    'application' => __('adoption.validation.existing_application'),
                ]);
            }

            $application = AdoptionApplication::query()->create([
                'adoption_case_id' => $lockedCase->id,
                'applicant_user_id' => $applicant->id,
                'idempotency_key' => $idempotencyKey,
                'placement_type' => $data->placementType,
                'status' => 'submitted',
                'identity_status' => 'unverified',
                'message' => $data->message,
                'private_profile' => $data->privateProfile,
                'terms_accepted' => $data->termsAccepted,
                'privacy_accepted' => $data->privacyAccepted,
                'reference_contact_consent' => $data->referenceContactConsent,
                'submitted_at' => now(),
            ]);

            AdoptionEvent::query()->create([
                'adoption_case_id' => $lockedCase->id,
                'adoption_application_id' => $application->id,
                'actor_user_id' => $applicant->id,
                'event_type' => 'application-submitted',
                'current_status' => $application->status->value,
                'reason_translation_key' => 'adoption.events.application_submitted',
                'metadata' => ['placement_type' => $application->placement_type->value],
            ]);

            return $application;
        });
    }
}
