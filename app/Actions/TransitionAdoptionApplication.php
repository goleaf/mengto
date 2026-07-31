<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AdoptionApplicationStatus;
use App\Enums\AdoptionCaseStatus;
use App\Models\AdoptionApplication;
use App\Models\AdoptionCase;
use App\Models\AdoptionEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class TransitionAdoptionApplication
{
    public function __construct(private readonly Gate $gate) {}

    public function handle(
        User $actor,
        AdoptionApplication $application,
        AdoptionApplicationStatus $target,
        int $expectedVersion,
    ): AdoptionApplication {
        $this->gate->forUser($actor)->authorize('transition', $application);

        return DB::transaction(function () use (
            $actor,
            $application,
            $target,
            $expectedVersion,
        ): AdoptionApplication {
            $locked = AdoptionApplication::query()
                ->with('adoptionCase.listing:id,owner_key,status,moderation_status')
                ->lockForUpdate()
                ->findOrFail($application->id);

            $this->gate->forUser($actor)->authorize('transition', $locked);

            if ($locked->lock_version !== $expectedVersion) {
                throw ValidationException::withMessages([
                    'application' => __('adoption.validation.stale_application'),
                ]);
            }

            if (
                $locked->applicant_user_id === $actor->id
                && ! $actor->isAdministrator()
                && $locked->adoptionCase->listing->owner_key !== $actor->actor_key
                && $target !== AdoptionApplicationStatus::Withdrawn
            ) {
                throw ValidationException::withMessages([
                    'application' => __('adoption.validation.applicant_transition'),
                ]);
            }

            $previous = $locked->status;

            if (! $previous->canTransitionTo($target)) {
                throw ValidationException::withMessages([
                    'application' => __('adoption.validation.invalid_transition'),
                ]);
            }

            $locked->forceFill([
                'status' => $target,
                'reviewer_user_id' => $locked->applicant_user_id === $actor->id
                    ? $locked->reviewer_user_id
                    : $actor->id,
                'lock_version' => $locked->lock_version + 1,
                ...$this->timestamps($target),
            ])->save();

            $this->updateCaseStatus($locked->adoptionCase, $target);

            AdoptionEvent::query()->create([
                'adoption_case_id' => $locked->adoption_case_id,
                'adoption_application_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'event_type' => 'application-status-changed',
                'previous_status' => $previous->value,
                'current_status' => $target->value,
                'reason_translation_key' => 'adoption.events.application_status_changed',
                'metadata' => ['lock_version' => $locked->lock_version],
            ]);

            return $locked->refresh();
        });
    }

    /** @return array<string, mixed> */
    private function timestamps(AdoptionApplicationStatus $target): array
    {
        return match ($target) {
            AdoptionApplicationStatus::Screening => [
                'reviewed_at' => now(),
                'closed_at' => null,
            ],
            AdoptionApplicationStatus::HomeCheck,
            AdoptionApplicationStatus::References => ['reviewed_at' => now()],
            AdoptionApplicationStatus::Meeting => ['meeting_at' => now()],
            AdoptionApplicationStatus::Reserved => ['reserved_at' => now()],
            AdoptionApplicationStatus::ContractPending => ['contracted_at' => now()],
            AdoptionApplicationStatus::Trial => ['trial_started_at' => now()],
            AdoptionApplicationStatus::FollowUp => ['follow_up_at' => now()],
            AdoptionApplicationStatus::Closed,
            AdoptionApplicationStatus::Declined,
            AdoptionApplicationStatus::Withdrawn,
            AdoptionApplicationStatus::Failed => ['closed_at' => now()],
            default => [],
        };
    }

    private function updateCaseStatus(
        AdoptionCase $case,
        AdoptionApplicationStatus $target,
    ): void {
        $status = match ($target) {
            AdoptionApplicationStatus::Screening,
            AdoptionApplicationStatus::HomeCheck,
            AdoptionApplicationStatus::References,
            AdoptionApplicationStatus::Meeting => AdoptionCaseStatus::Screening,
            AdoptionApplicationStatus::Reserved,
            AdoptionApplicationStatus::ContractPending => AdoptionCaseStatus::Reserved,
            AdoptionApplicationStatus::Trial => AdoptionCaseStatus::Trial,
            AdoptionApplicationStatus::Adopted,
            AdoptionApplicationStatus::FollowUp => AdoptionCaseStatus::Adopted,
            AdoptionApplicationStatus::FosterPlaced,
            AdoptionApplicationStatus::Transferred => AdoptionCaseStatus::Fostered,
            AdoptionApplicationStatus::Returned => AdoptionCaseStatus::Returned,
            AdoptionApplicationStatus::Failed => AdoptionCaseStatus::Failed,
            default => null,
        };

        if ($status === null || $case->status === $status) {
            return;
        }

        $case->forceFill([
            'status' => $status,
            'lock_version' => $case->lock_version + 1,
        ])->save();
    }
}
