<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceStatus;
use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionResolution;
use App\Enums\PlaceSubmissionStatus;
use App\Models\Place;
use App\Models\PlaceMergeRedirect;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\PlaceSubmissionTransition;
use Illuminate\Validation\ValidationException;

final readonly class RestoreMergedPlace
{
    public function __construct(private PlaceSubmissionTransition $transition) {}

    public function handle(
        User $actor,
        PlaceMergeRedirect $redirect,
        string $operationKey,
        int $expectedLockVersion,
        string $reasonCode,
    ): PlaceMergeRedirect {
        $mergeEvent = $redirect->event()
            ->where('action', PlaceSubmissionAction::PlacesMerged->value)
            ->where('destination_place_id', $redirect->destination_place_id)
            ->firstOrFail(['id', 'place_submission_id']);

        $submission = PlaceSubmission::query()
            ->whereKey($mergeEvent->place_submission_id)
            ->firstOrFail();

        $this->transition->handle(
            $actor,
            $submission,
            'restore',
            [PlaceSubmissionStatus::Published],
            PlaceSubmissionStatus::Published,
            PlaceSubmissionAction::MergeRestored,
            $operationKey,
            $expectedLockVersion,
            $reasonCode,
            destinationPlaceId: $redirect->destination_place_id,
            resolution: PlaceSubmissionResolution::NewPlace,
            mutate: static function (PlaceSubmission $locked) use ($actor, $redirect, $mergeEvent): void {
                $lockedRedirect = PlaceMergeRedirect::query()->lockForUpdate()->findOrFail($redirect->id);

                if ($locked->resolution !== PlaceSubmissionResolution::DuplicateMerge
                    || $locked->linked_place_id !== $redirect->destination_place_id
                    || $lockedRedirect->place_submission_event_id !== $mergeEvent->id
                    || $lockedRedirect->restored_at !== null
                    || $lockedRedirect->active_source_identifier === null) {
                    throw ValidationException::withMessages(['redirect' => __('places.submissions.validation.already_restored')]);
                }

                $eventRedirects = PlaceMergeRedirect::query()
                    ->where('place_submission_event_id', $mergeEvent->id)
                    ->whereNull('restored_at')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                foreach ($eventRedirects->groupBy('source_place_id') as $sourceId => $sourceRedirects) {
                    $source = Place::query()->lockForUpdate()->findOrFail((int) $sourceId);

                    if ($source->status !== PlaceStatus::Merged
                        || $source->merged_into_place_id !== $redirect->destination_place_id) {
                        throw ValidationException::withMessages(['redirect' => __('places.submissions.validation.redirect')]);
                    }

                    foreach ($sourceRedirects as $eventRedirect) {
                        $eventRedirect->active_source_identifier = null;
                        $eventRedirect->restored_by_user_id = $actor->id;
                        $eventRedirect->restored_at = now()->toImmutable();
                        $eventRedirect->save();
                    }

                    if ($source->id === $locked->published_place_id) {
                        $source->status = PlaceStatus::Active;
                        $source->merged_into_place_id = null;
                    } else {
                        $predecessors = PlaceMergeRedirect::query()
                            ->where('source_place_id', $source->id)
                            ->where('destination_place_id', $locked->published_place_id)
                            ->whereNull('active_source_identifier')
                            ->whereNull('restored_at')
                            ->whereNotNull('superseded_at')
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->get();

                        if ($predecessors->isEmpty()) {
                            throw ValidationException::withMessages(['redirect' => __('places.submissions.validation.redirect')]);
                        }

                        foreach ($predecessors as $predecessor) {
                            $predecessor->active_source_identifier = $predecessor->source_identifier;
                            $predecessor->superseded_at = null;
                            $predecessor->save();
                        }

                        $source->merged_into_place_id = $locked->published_place_id;
                    }

                    $source->lock_version++;
                    $source->save();
                }

                $locked->linked_place_id = null;
            },
        );

        return $redirect->refresh();
    }
}
