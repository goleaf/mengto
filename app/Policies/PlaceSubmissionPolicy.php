<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\PlaceStatus;
use App\Enums\PlaceSubmissionStatus;
use App\Models\OrganizationMembership;
use App\Models\Place;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceSubmission;
use App\Models\User;

final class PlaceSubmissionPolicy
{
    public function create(?User $user): bool
    {
        return $this->eligible($user);
    }

    public function view(?User $user, PlaceSubmission $submission): bool
    {
        if (! $this->eligible($user)) {
            return false;
        }

        return $user->id === $submission->submitter_user_id
            || $user->isAdministrator()
            || $this->managesSubmissionScope($user, $submission);
    }

    public function update(?User $user, PlaceSubmission $submission): bool
    {
        return $this->eligible($user)
            && $user->id === $submission->submitter_user_id
            && $submission->status->isOpen();
    }

    public function chooseDuplicate(?User $user, PlaceSubmission $submission): bool
    {
        return $this->eligible($user)
            && $user->id === $submission->submitter_user_id
            && $submission->status === PlaceSubmissionStatus::DuplicateReview;
    }

    public function respond(?User $user, PlaceSubmission $submission): bool
    {
        return $this->eligible($user)
            && $user->id === $submission->submitter_user_id
            && $submission->status === PlaceSubmissionStatus::NeedsInformation;
    }

    public function withdraw(?User $user, PlaceSubmission $submission): bool
    {
        return $this->eligible($user)
            && $user->id === $submission->submitter_user_id
            && in_array($submission->status, [
                PlaceSubmissionStatus::Submitted,
                PlaceSubmissionStatus::NeedsInformation,
                PlaceSubmissionStatus::DuplicateReview,
                PlaceSubmissionStatus::Approved,
            ], true);
    }

    public function review(?User $user, PlaceSubmission $submission): bool
    {
        return $this->eligible($user)
            && $user->id !== $submission->submitter_user_id
            && ($user->isAdministrator() || $this->managesSubmissionScope($user, $submission));
    }

    public function approveNewPlace(?User $user, PlaceSubmission $submission): bool
    {
        return $this->independentAdministrator($user, $submission);
    }

    public function requestInformation(
        ?User $user,
        PlaceSubmission $submission,
        ?PlaceDuplicateCandidate $candidate = null,
    ): bool {
        if ($this->independentAdministrator($user, $submission)) {
            return true;
        }

        return $this->eligible($user)
            && $user->id !== $submission->submitter_user_id
            && $candidate !== null
            && $candidate->place_submission_id === $submission->id
            && $candidate->candidate_place_id !== null
            && $this->managesPlace($user, $candidate->candidate_place_id);
    }

    public function linkExisting(
        ?User $user,
        PlaceSubmission $submission,
        PlaceDuplicateCandidate $candidate,
    ): bool {
        return $candidate->place_submission_id === $submission->id
            && $candidate->candidate_place_id !== null
            && ($this->independentAdministrator($user, $submission)
                || ($this->eligible($user)
                    && $user->id !== $submission->submitter_user_id
                    && $this->managesPlace($user, $candidate->candidate_place_id)));
    }

    public function reject(?User $user, PlaceSubmission $submission): bool
    {
        return $this->independentAdministrator($user, $submission);
    }

    public function reopen(?User $user, PlaceSubmission $submission): bool
    {
        return $this->independentAdministrator($user, $submission);
    }

    public function publish(?User $user, PlaceSubmission $submission): bool
    {
        return $this->independentAdministrator($user, $submission);
    }

    public function merge(?User $user, PlaceSubmission $submission): bool
    {
        return $this->publish($user, $submission);
    }

    public function restore(?User $user, PlaceSubmission $submission): bool
    {
        return $this->publish($user, $submission);
    }

    private function independentAdministrator(?User $user, PlaceSubmission $submission): bool
    {
        return $this->eligible($user)
            && $user->id !== $submission->submitter_user_id
            && $user->isAdministrator();
    }

    private function eligible(?User $user): bool
    {
        return $user?->isActive() === true && $user->hasVerifiedEmail();
    }

    private function managesSubmissionScope(User $user, PlaceSubmission $submission): bool
    {
        $organizationId = $submission->canonical_organization_id
            ?? $submission->linkedPlace()->value('organization_id');

        if ($organizationId !== null && $this->managesOrganization($user, $organizationId)) {
            return true;
        }

        return $submission->duplicateCandidates()
            ->whereNotNull('candidate_place_id')
            ->whereHas('candidatePlace', fn ($places) => $places
                ->where('status', PlaceStatus::Active->value)
                ->whereNull('archived_at')
                ->where(function ($managed) use ($user): void {
                    $managed
                        ->where('owner_user_id', $user->id)
                        ->orWhereHas('organization', fn ($organization) => $organization
                            ->where('status', OrganizationStatus::Active->value)
                            ->whereNull('archived_at')
                            ->whereHas('activeMemberships', fn ($memberships) => $memberships
                                ->where('user_id', $user->id)
                                ->whereIn('role', OrganizationRole::placeManagerValues())));
                }))
            ->exists();
    }

    private function managesOrganization(User $user, int $organizationId): bool
    {
        return OrganizationMembership::query()
            ->active()
            ->where('organization_id', $organizationId)
            ->where('user_id', $user->id)
            ->whereHas('organization', fn ($query) => $query
                ->where('status', OrganizationStatus::Active->value)
                ->whereNull('archived_at'))
            ->whereIn('role', OrganizationRole::placeManagerValues())
            ->exists();
    }

    private function managesPlace(User $user, int $placeId): bool
    {
        $place = Place::query()->select(['id', 'organization_id', 'owner_user_id', 'status'])->find($placeId);

        return $place !== null
            && $place->status->value === 'active'
            && $place->isManagedBy($user);
    }
}
