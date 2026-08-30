<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceStatus;
use App\Enums\PlaceVisibility;
use App\Models\Place;
use App\Models\PlaceMergeRedirect;
use App\Models\User;
use App\Policies\PlacePolicy;

final readonly class ResolvePlaceMergeRedirect
{
    public function __construct(private PlacePolicy $policy) {}

    public function handle(?User $actor, string $identifier): ?Place
    {
        $redirect = PlaceMergeRedirect::query()
            ->where('active_source_identifier', $identifier)
            ->whereNull('restored_at')
            ->with(['sourcePlace.organization', 'destinationPlace.organization'])
            ->first();

        if ($redirect === null) {
            return null;
        }

        $source = $redirect->sourcePlace;
        $destination = $redirect->destinationPlace;

        if ($source->status !== PlaceStatus::Merged
            || $source->merged_into_place_id !== $destination->id
            || $destination->status !== PlaceStatus::Active
            || ! $this->canDiscloseSource($actor, $source, $redirect->source_visibility)
            || ! $this->canViewDestination($actor, $destination)) {
            return null;
        }

        return $destination;
    }

    private function canDiscloseSource(?User $actor, Place $source, PlaceVisibility $visibilityCeiling): bool
    {
        return $actor === null
            ? $visibilityCeiling === PlaceVisibility::Public
                && $source->visibility === PlaceVisibility::Public
            : $this->policy->discloseMergedIdentifier($actor, $source, $visibilityCeiling);
    }

    private function canViewDestination(?User $actor, Place $destination): bool
    {
        return $actor === null
            ? $destination->visibility === PlaceVisibility::Public
            : $this->policy->view($actor, $destination);
    }
}
