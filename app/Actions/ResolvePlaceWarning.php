<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceWarningResolution;
use App\Enums\PlaceWarningStatus;
use App\Models\PlaceWarning;
use App\Models\PlaceWarningEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ResolvePlaceWarning
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        PlaceWarning $warning,
        PlaceWarningStatus $status,
        ?PlaceWarningResolution $resolution,
        string $reason,
    ): PlaceWarning {
        $this->validateDecision($status, $resolution, $reason);
        $warning->loadMissing('place');
        $this->gate->forUser($actor)->authorize('resolve', $warning);

        return DB::transaction(function () use ($actor, $warning, $status, $resolution, $reason): PlaceWarning {
            $locked = PlaceWarning::query()->with('place')->lockForUpdate()->findOrFail($warning->id);
            $this->gate->forUser($actor)->authorize('resolve', $locked);
            $from = $locked->status;
            if (in_array($from, [PlaceWarningStatus::Expired, PlaceWarningStatus::Resolved, PlaceWarningStatus::Rejected, PlaceWarningStatus::Removed], true)) {
                throw ValidationException::withMessages([
                    'place_warning' => __('places.validation.warning_not_resolvable'),
                ]);
            }

            $now = now();
            $locked->forceFill([
                'status' => $status,
                'resolution' => $resolution,
                'moderator_user_id' => $actor->id,
                'moderation_reason' => trim($reason),
                'published_at' => $status === PlaceWarningStatus::Published ? $now : $locked->published_at,
                'resolved_at' => $status === PlaceWarningStatus::Published ? null : $now,
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            PlaceWarningEvent::query()->create([
                'place_warning_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'event_type' => match ($status) {
                    PlaceWarningStatus::Published => 'published',
                    PlaceWarningStatus::Resolved => 'resolved',
                    PlaceWarningStatus::Rejected => 'rejected',
                    PlaceWarningStatus::Removed => 'removed',
                    default => 'moderated',
                },
                'from_status' => $from->value,
                'to_status' => $status->value,
                'public_summary_key' => 'places.warnings.events.'.$status->value,
                'private_note' => trim($reason),
            ]);

            return $locked;
        }, 3);
    }

    private function validateDecision(
        PlaceWarningStatus $status,
        ?PlaceWarningResolution $resolution,
        string $reason,
    ): void {
        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'place_warning' => __('places.validation.warning_resolution_invalid'),
            ]);
        }

        $valid = match ($status) {
            PlaceWarningStatus::Published => $resolution === null,
            PlaceWarningStatus::Resolved => in_array($resolution, [
                PlaceWarningResolution::ConditionEnded,
                PlaceWarningResolution::Corrected,
            ], true),
            PlaceWarningStatus::Rejected => in_array($resolution, [
                PlaceWarningResolution::FalseReport,
                PlaceWarningResolution::InsufficientEvidence,
            ], true),
            PlaceWarningStatus::Removed => $resolution === PlaceWarningResolution::Removed,
            default => false,
        };
        if (! $valid) {
            throw ValidationException::withMessages([
                'place_warning' => __('places.validation.warning_resolution_invalid'),
            ]);
        }
    }
}
