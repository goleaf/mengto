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
        $submission = PlaceSubmission::query()
            ->where('published_place_id', $redirect->source_place_id)
            ->where('linked_place_id', $redirect->destination_place_id)
            ->where('resolution', PlaceSubmissionResolution::DuplicateMerge->value)
            ->firstOrFail();

        if ($redirect->restored_at !== null) {
            throw ValidationException::withMessages(['redirect' => __('places.submissions.validation.already_restored')]);
        }

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
            mutate: static function (PlaceSubmission $locked) use ($actor, $redirect): void {
                $source = Place::query()->lockForUpdate()->findOrFail($redirect->source_place_id);

                if ($source->status !== PlaceStatus::Merged
                    || $source->merged_into_place_id !== $redirect->destination_place_id) {
                    throw ValidationException::withMessages(['redirect' => __('places.submissions.validation.redirect')]);
                }

                $source->status = PlaceStatus::Active;
                $source->merged_into_place_id = null;
                $source->lock_version++;
                $source->save();

                PlaceMergeRedirect::query()
                    ->where('source_place_id', $source->id)
                    ->whereNull('restored_at')
                    ->update([
                        'restored_by_user_id' => $actor->id,
                        'restored_at' => now(),
                    ]);

                $locked->linked_place_id = null;
            },
        );

        return $redirect->refresh();
    }
}
