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

final class CloseAdoptionCase
{
    public function __construct(private readonly Gate $gate) {}

    public function handle(User $actor, AdoptionCase $case, int $expectedVersion): AdoptionCase
    {
        $this->gate->forUser($actor)->authorize('manage', $case);

        return DB::transaction(function () use ($actor, $case, $expectedVersion): AdoptionCase {
            $lockedCase = AdoptionCase::query()
                ->with('listing:id,owner_key,status,moderation_status')
                ->lockForUpdate()
                ->findOrFail($case->id);
            $this->gate->forUser($actor)->authorize('manage', $lockedCase);

            if ($lockedCase->lock_version !== $expectedVersion) {
                throw ValidationException::withMessages([
                    'case' => __('adoption.validation.stale_case'),
                ]);
            }

            if (in_array($lockedCase->status, [
                AdoptionCaseStatus::Closed,
                AdoptionCaseStatus::Archived,
            ], true)) {
                return $lockedCase;
            }

            $applications = AdoptionApplication::query()
                ->where('adoption_case_id', $lockedCase->id)
                ->lockForUpdate()
                ->get();

            foreach ($applications as $application) {
                if ($application->status === AdoptionApplicationStatus::Closed) {
                    continue;
                }

                $previous = $application->status;
                $application->forceFill([
                    'status' => AdoptionApplicationStatus::Closed,
                    'reviewer_user_id' => $actor->id,
                    'lock_version' => $application->lock_version + 1,
                    'closed_at' => now(),
                ])->save();

                AdoptionEvent::query()->create([
                    'adoption_case_id' => $lockedCase->id,
                    'adoption_application_id' => $application->id,
                    'actor_user_id' => $actor->id,
                    'event_type' => 'application-closed-with-case',
                    'previous_status' => $previous->value,
                    'current_status' => AdoptionApplicationStatus::Closed->value,
                    'reason_translation_key' => 'adoption.events.case_closed',
                ]);
            }

            $previousStatus = $lockedCase->status;
            $lockedCase->forceFill([
                'status' => AdoptionCaseStatus::Closed,
                'lock_version' => $lockedCase->lock_version + 1,
                'closed_at' => now(),
            ])->save();

            AdoptionEvent::query()->create([
                'adoption_case_id' => $lockedCase->id,
                'actor_user_id' => $actor->id,
                'event_type' => 'case-closed',
                'previous_status' => $previousStatus->value,
                'current_status' => AdoptionCaseStatus::Closed->value,
                'reason_translation_key' => 'adoption.events.case_closed',
            ]);

            return $lockedCase->refresh();
        });
    }
}
