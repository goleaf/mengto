<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\ConfirmPlaceDuplicateCandidate;
use App\Actions\ContinueDistinctPlaceSubmission;
use App\Actions\RespondToPlaceSubmissionInformation;
use App\Actions\WithdrawPlaceSubmission;
use App\Enums\PlaceStatus;
use App\Enums\PlaceVisibility;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PlaceSubmissionStatusPage extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public int $submissionId;

    #[Locked]
    public string $operationKey = '';

    public string $responseDetail = '';

    private AuthFactory $auth;

    private ProfilePresenter $profiles;

    public function boot(AuthFactory $auth, ProfilePresenter $profiles): void
    {
        $this->auth = $auth;
        $this->profiles = $profiles;
    }

    public function mount(PlaceSubmission $placeSubmission): void
    {
        $this->authorize('view', $placeSubmission);
        $this->submissionId = $placeSubmission->id;
        $this->operationKey = (string) Str::uuid();
    }

    #[Computed]
    public function submission(): PlaceSubmission
    {
        $submission = PlaceSubmission::query()
            ->with(['publishedPlace:id,slug,name', 'linkedPlace:id,slug,name'])
            ->findOrFail($this->submissionId);
        $this->authorize('view', $submission);

        return $submission;
    }

    /** @return list<array{key: string, name: string, region: string, url: string, correction_url: string}> */
    #[Computed]
    public function visibleCandidates(): array
    {
        $submission = $this->submission();
        $this->authorize('view', $submission);

        return $submission->duplicateCandidates()
            ->where('presentation_scope', 'member_visible')
            ->whereHas('candidatePlace', static fn ($query) => $query
                ->where('visibility', PlaceVisibility::Public->value)
                ->where('status', PlaceStatus::Active->value))
            ->with('candidatePlace:id,stable_key,slug,name,public_region')
            ->orderByDesc('score')
            ->limit(5)
            ->get(['id', 'candidate_key', 'candidate_place_id'])
            ->map(static fn ($candidate): array => [
                'key' => $candidate->candidate_key,
                'name' => $candidate->candidatePlace->name,
                'region' => $candidate->candidatePlace->public_region,
                'url' => route('places.show', ['place' => $candidate->candidatePlace->slug]),
                'correction_url' => route('compose', [
                    'kind' => 'place-correction',
                    'target' => $candidate->candidatePlace->slug,
                ]),
            ])
            ->all();
    }

    public function confirmCandidate(string $candidateKey, ConfirmPlaceDuplicateCandidate $action): void
    {
        $submission = $this->submission();
        $candidate = $submission->duplicateCandidates()
            ->where('candidate_key', $candidateKey)
            ->where('presentation_scope', 'member_visible')
            ->whereNotNull('candidate_place_id')
            ->firstOrFail();

        $action->handle(
            $this->requireUser(),
            $submission,
            $candidate,
            $this->operationKey,
            $submission->lock_version,
        );
        $this->completed('candidate_confirmed');
    }

    public function continueDistinct(ContinueDistinctPlaceSubmission $action): void
    {
        $submission = $this->submission();
        $action->handle(
            $this->requireUser(),
            $submission,
            $this->operationKey,
            $submission->lock_version,
        );
        $this->completed('continued');
    }

    public function respond(RespondToPlaceSubmissionInformation $action): void
    {
        $submission = $this->submission();
        $action->handle(
            $this->requireUser(),
            $submission,
            $this->operationKey,
            $submission->lock_version,
            $this->responseDetail,
        );
        $this->responseDetail = '';
        $this->completed('information_provided');
    }

    public function withdraw(WithdrawPlaceSubmission $action): void
    {
        $submission = $this->submission();
        $action->handle(
            $this->requireUser(),
            $submission,
            $this->operationKey,
            $submission->lock_version,
        );
        $this->completed('withdrawn');
    }

    public function render(): View
    {
        $user = $this->auth->guard('web')->user();
        abort_unless($user instanceof User, 403);

        return view('livewire.places.place-submission-status-page')
            ->layout('components.livewire-app-layout', [
                'owner' => $this->profiles->owner(),
                'title' => __('places.submissions.status.title'),
                'activeSection' => 'places',
            ]);
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function completed(string $feedback): void
    {
        session()->flash('place-submission-feedback', __('places.submissions.feedback.'.$feedback));
        $this->operationKey = (string) Str::uuid();
        unset($this->submission, $this->visibleCandidates);
    }
}
