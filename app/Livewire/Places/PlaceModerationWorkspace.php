<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\ApprovePlaceSubmission;
use App\Actions\LinkPlaceSubmission;
use App\Actions\MergePlaceDuplicate;
use App\Actions\PublishPlaceSubmission;
use App\Actions\RejectPlaceSubmission;
use App\Actions\ReopenPlaceSubmission;
use App\Actions\RequestPlaceSubmissionInformation;
use App\Actions\RestoreMergedPlace;
use App\Enums\PlaceSubmissionResolution;
use App\Enums\PlaceSubmissionStatus;
use App\Models\Place;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\LocaleFormatter;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
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

    private LocaleFormatter $formatter;

    public function boot(AuthFactory $auth, ProfilePresenter $profiles, LocaleFormatter $formatter): void
    {
        $this->auth = $auth;
        $this->profiles = $profiles;
        $this->formatter = $formatter;
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
            ->where(function ($queue): void {
                $queue->whereIn('status', [
                    PlaceSubmissionStatus::Submitted->value,
                    PlaceSubmissionStatus::DuplicateReview->value,
                    PlaceSubmissionStatus::NeedsInformation->value,
                    PlaceSubmissionStatus::Approved->value,
                    PlaceSubmissionStatus::Rejected->value,
                    PlaceSubmissionStatus::Withdrawn->value,
                ])->orWhere(function ($published): void {
                    $published
                        ->where('status', PlaceSubmissionStatus::Published->value)
                        ->where(function ($actionable): void {
                            $actionable
                                ->where('resolution', PlaceSubmissionResolution::DuplicateMerge->value)
                                ->orWhereHas('duplicateCandidates', fn ($candidates) => $candidates
                                    ->whereNotNull('candidate_place_id'));
                        });
                });
            })
            ->with([
                'submitter:id,name',
                'duplicateCandidates.candidatePlace:id,name,public_region',
                'mergeRedirects' => fn ($redirects) => $redirects
                    ->whereNotNull('active_source_identifier')
                    ->whereNull('restored_at'),
            ])
            ->orderBy('submitted_at')
            ->limit(50)
            ->get()
            ->map(fn (PlaceSubmission $submission): array => [
                'key' => $submission->stable_key,
                'name' => $submission->name,
                'region' => $submission->public_region,
                'submitter' => $submission->submitter->name,
                'status' => $submission->status->value,
                'version' => $submission->lock_version,
                'review_rows' => $this->reviewRows($submission),
                'facts' => $this->factLines($submission),
                'can_merge' => $submission->status === PlaceSubmissionStatus::Published
                    && $submission->resolution !== PlaceSubmissionResolution::DuplicateMerge
                    && $submission->published_place_id !== null,
                'can_restore' => $submission->status === PlaceSubmissionStatus::Published
                    && $submission->resolution === PlaceSubmissionResolution::DuplicateMerge
                    && $submission->mergeRedirects->isNotEmpty(),
                'restore_key' => $submission->mergeRedirects->first()?->active_source_identifier,
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

    public function merge(string $submissionKey, string $candidateKey, MergePlaceDuplicate $action): void
    {
        $submission = $this->submission($submissionKey);
        $candidate = $submission->duplicateCandidates()->where('candidate_key', $candidateKey)->firstOrFail();
        $source = Place::query()->findOrFail($submission->published_place_id);
        $action->handle(
            $this->requireUser(),
            $submission,
            $source,
            $candidate,
            $this->operationKey,
            $submission->lock_version,
            $this->reason(),
        );
        $this->completed();
    }

    public function restore(string $submissionKey, string $redirectKey, RestoreMergedPlace $action): void
    {
        $submission = $this->submission($submissionKey);
        $redirect = $submission->mergeRedirects()
            ->where('active_source_identifier', $redirectKey)
            ->whereNull('restored_at')
            ->firstOrFail();
        $action->handle(
            $this->requireUser(),
            $redirect,
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

    /** @return list<array{label: string, value: string}> */
    private function reviewRows(PlaceSubmission $submission): array
    {
        $contact = implode(' · ', array_values(array_filter([
            $submission->public_phone,
            $submission->public_email,
            $submission->public_website,
        ], static fn (mixed $value): bool => filled($value))));
        $publicLocation = implode(' · ', array_values(array_filter([
            $submission->public_region,
            $submission->public_address,
            $submission->public_latitude !== null && $submission->public_longitude !== null
                ? $submission->public_latitude.', '.$submission->public_longitude
                : null,
        ], static fn (mixed $value): bool => filled($value))));
        $privateLocation = implode(' · ', array_values(array_filter([
            $submission->exact_address,
            $submission->exact_latitude !== null && $submission->exact_longitude !== null
                ? $submission->exact_latitude.', '.$submission->exact_longitude
                : null,
        ], static fn (mixed $value): bool => filled($value))));
        $audit = is_array($submission->audit_context) ? implode(' · ', array_filter([
            $submission->audit_context['request_id'] ?? null,
            $submission->audit_context['channel'] ?? null,
        ])) : '';

        $rows = [
            [__('places.submissions.review.source'), $submission->source_kind->label()],
            [__('places.submissions.review.source_reference'), $submission->source_reference],
            [__('places.submissions.review.relationship'), __('places.submissions.relationships.'.$submission->relationship_to_place)],
            [__('places.submissions.review.precision'), $submission->location_precision->label()],
            [__('places.submissions.review.public_location'), $publicLocation],
            [__('places.submissions.review.private_location'), $privateLocation],
            [__('places.submissions.review.summary'), $submission->summary],
            [__('places.submissions.review.contact'), $contact],
            [__('places.submissions.review.observed'), $this->formatter->date($submission->observed_at)],
            [__('places.submissions.review.submitted'), $this->formatter->dateTime($submission->submitted_at)],
            [__('places.submissions.review.consent'), $submission->consent_version.' · '.$this->formatter->dateTime($submission->consented_at)],
            [__('places.submissions.review.audit'), $audit],
        ];
        $reviewRows = [];

        foreach ($rows as [$label, $value]) {
            if (filled($value)) {
                $reviewRows[] = ['label' => (string) $label, 'value' => (string) $value];
            }
        }

        return $reviewRows;
    }

    /** @return list<array{label: string, value: string}> */
    private function factLines(PlaceSubmission $submission): array
    {
        return collect(Arr::dot(is_array($submission->submitted_facts) ? $submission->submitted_facts : []))
            ->map(function (mixed $value, string $key): array {
                $field = Str::before($key, '.');

                return [
                    'label' => __('places.submissions.fields.'.$field),
                    'value' => is_scalar($value) ? (string) $value : '',
                ];
            })
            ->filter(static fn (array $fact): bool => $fact['value'] !== '')
            ->values()
            ->all();
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();
        abort_unless($user instanceof User && $user->isActive() && $user->hasVerifiedEmail(), 403);

        return $user;
    }
}
