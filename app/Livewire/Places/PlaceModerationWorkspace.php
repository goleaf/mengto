<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\ApprovePlaceSubmission;
use App\Actions\LinkPlaceSubmission;
use App\Actions\PublishPlaceSubmission;
use App\Actions\RejectPlaceSubmission;
use App\Actions\ReopenPlaceSubmission;
use App\Actions\RequestPlaceSubmissionInformation;
use App\Enums\PlaceSubmissionStatus;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PlaceModerationWorkspace extends Component
{
    #[Locked]
    public string $operationKey = '';

    public string $reasonCode = 'community-review';

    public string $reasonDetail = '';

    private AuthFactory $auth;

    private ProfilePresenter $profiles;

    public function boot(AuthFactory $auth, ProfilePresenter $profiles): void
    {
        $this->auth = $auth;
        $this->profiles = $profiles;
    }

    public function mount(): void
    {
        abort_unless($this->requireUser()->isAdministrator(), 403);
        $this->operationKey = (string) Str::uuid();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function submissions(): array
    {
        abort_unless($this->requireUser()->isAdministrator(), 403);

        return PlaceSubmission::query()
            ->whereIn('status', [
                PlaceSubmissionStatus::Submitted->value,
                PlaceSubmissionStatus::DuplicateReview->value,
                PlaceSubmissionStatus::NeedsInformation->value,
                PlaceSubmissionStatus::Approved->value,
                PlaceSubmissionStatus::Rejected->value,
            ])
            ->with(['submitter:id,name', 'duplicateCandidates.candidatePlace:id,name,public_region'])
            ->orderBy('submitted_at')
            ->limit(50)
            ->get()
            ->map(static fn (PlaceSubmission $submission): array => [
                'key' => $submission->stable_key,
                'name' => $submission->name,
                'region' => $submission->public_region,
                'submitter' => $submission->submitter->name,
                'status' => $submission->status->value,
                'version' => $submission->lock_version,
                'candidates' => $submission->duplicateCandidates
                    ->filter(static fn (PlaceDuplicateCandidate $candidate): bool => $candidate->candidatePlace !== null)
                    ->map(static fn (PlaceDuplicateCandidate $candidate): array => [
                        'key' => $candidate->candidate_key,
                        'name' => $candidate->candidatePlace->name,
                        'region' => $candidate->candidatePlace->public_region,
                    ])->values()->all(),
            ])->all();
    }

    public function approve(string $submissionKey, ApprovePlaceSubmission $action): void
    {
        $submission = $this->submission($submissionKey);
        $action->handle($this->requireUser(), $submission, $this->operationKey, $submission->lock_version, $this->reason());
        $this->completed();
    }

    public function publish(string $submissionKey, PublishPlaceSubmission $action): void
    {
        $submission = $this->submission($submissionKey);
        $action->handle($this->requireUser(), $submission, $this->operationKey, $submission->lock_version);
        $this->completed();
    }

    public function requestInformation(string $submissionKey, RequestPlaceSubmissionInformation $action): void
    {
        $submission = $this->submission($submissionKey);
        $action->handle(
            $this->requireUser(),
            $submission,
            $this->operationKey,
            $submission->lock_version,
            $this->reason(),
            $this->detail(),
        );
        $this->completed();
    }

    public function reject(string $submissionKey, RejectPlaceSubmission $action): void
    {
        $submission = $this->submission($submissionKey);
        $action->handle(
            $this->requireUser(),
            $submission,
            $this->operationKey,
            $submission->lock_version,
            $this->reason(),
            $this->detail(),
        );
        $this->completed();
    }

    public function reopen(string $submissionKey, ReopenPlaceSubmission $action): void
    {
        $submission = $this->submission($submissionKey);
        $action->handle($this->requireUser(), $submission, $this->operationKey, $submission->lock_version, $this->reason());
        $this->completed();
    }

    public function link(string $submissionKey, string $candidateKey, LinkPlaceSubmission $action): void
    {
        $submission = $this->submission($submissionKey);
        $candidate = $submission->duplicateCandidates()->where('candidate_key', $candidateKey)->firstOrFail();
        $action->handle(
            $this->requireUser(),
            $submission,
            $candidate,
            $this->operationKey,
            $submission->lock_version,
            $this->reason(),
        );
        $this->completed();
    }

    public function render(): View
    {
        return view('livewire.places.place-moderation-workspace')
            ->layout('components.livewire-app-layout', [
                'owner' => $this->profiles->owner(),
                'title' => __('places.submissions.moderation.title'),
                'activeSection' => 'places',
            ]);
    }

    private function submission(string $stableKey): PlaceSubmission
    {
        abort_unless($this->requireUser()->isAdministrator(), 403);

        return PlaceSubmission::query()->where('stable_key', $stableKey)->firstOrFail();
    }

    private function completed(): void
    {
        $this->operationKey = (string) Str::uuid();
        $this->reasonDetail = '';
        unset($this->submissions);
        session()->flash('place-moderation-feedback', __('places.submissions.moderation.saved'));
    }

    private function reason(): string
    {
        return Str::of($this->reasonCode)->lower()->replaceMatches('/[^a-z0-9]+/', '-')->trim('-')->limit(80, '')->toString()
            ?: 'community-review';
    }

    private function detail(): string
    {
        return Str::limit(trim($this->reasonDetail), 2000, '');
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();
        abort_unless($user instanceof User && $user->isActive() && $user->hasVerifiedEmail(), 403);

        return $user;
    }
}
