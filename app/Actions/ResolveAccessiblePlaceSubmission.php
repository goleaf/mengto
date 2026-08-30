<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\PlaceStatus;
use App\Models\PlaceSubmission;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final readonly class ResolveAccessiblePlaceSubmission
{
    public function __construct(private Gate $gate) {}

    public function handle(User $actor, string $stableKey): PlaceSubmission
    {
        $query = PlaceSubmission::query()->where('stable_key', $stableKey);

        if (! $actor->isAdministrator()) {
            $query->where(function (Builder $visible) use ($actor): void {
                $visible
                    ->where('submitter_user_id', $actor->id)
                    ->orWhereHas('canonicalOrganization', fn (Builder $organization) => $this
                        ->managedOrganization($organization, $actor))
                    ->orWhereHas('linkedPlace', fn (Builder $place) => $this->managedPlace($place, $actor))
                    ->orWhereHas('duplicateCandidates.candidatePlace', fn (Builder $place) => $this
                        ->managedPlace($place, $actor));
            });
        }

        $submission = $query->first();
        abort_unless($submission instanceof PlaceSubmission, 404);
        $this->gate->forUser($actor)->authorize('view', $submission);

        return $submission;
    }

    /** @param Builder<Model> $query */
    private function managedOrganization(Builder $query, User $actor): void
    {
        $query
            ->where('status', OrganizationStatus::Active->value)
            ->whereNull('archived_at')
            ->whereHas('activeMemberships', fn (Builder $memberships) => $memberships
                ->where('user_id', $actor->id)
                ->whereIn('role', OrganizationRole::placeManagerValues()));
    }

    /** @param Builder<Model> $query */
    private function managedPlace(Builder $query, User $actor): void
    {
        $query
            ->where('status', PlaceStatus::Active->value)
            ->whereNull('archived_at')
            ->where(function (Builder $managed) use ($actor): void {
                $managed
                    ->where('owner_user_id', $actor->id)
                    ->orWhereHas('organization', fn (Builder $organization) => $this
                        ->managedOrganization($organization, $actor));
            });
    }
}
