<?php

declare(strict_types=1);

namespace App\Livewire\Places;

use App\Actions\SubmitPlaceSubmission;
use App\Enums\PlaceLocationPrecision;
use App\Enums\PlaceSubmissionSource;
use App\Livewire\Forms\PlaceSubmissionForm;
use App\Models\PlaceSubmission;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class CreatePlaceSubmission extends Component
{
    use AuthorizesRequests;

    public PlaceSubmissionForm $form;

    #[Locked]
    public string $idempotencyKey = '';

    private AuthFactory $auth;

    private SubmitPlaceSubmission $submitPlace;

    public function boot(
        AuthFactory $auth,
        SubmitPlaceSubmission $submitPlace,
    ): void {
        $this->auth = $auth;
        $this->submitPlace = $submitPlace;
    }

    public function mount(): void
    {
        $this->authorize('create', PlaceSubmission::class);
        $this->idempotencyKey = (string) Str::uuid();
    }

    /** @return array<string, string> */
    #[Computed]
    public function categoryOptions(): array
    {
        return collect(array_keys(PlaceSubmissionForm::categoryTypes()))
            ->mapWithKeys(static fn (string $category): array => [
                $category => __('places.submissions.categories.'.$category),
            ])->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function sourceOptions(): array
    {
        return collect(PlaceSubmissionSource::cases())
            ->mapWithKeys(static fn (PlaceSubmissionSource $source): array => [$source->value => $source->label()])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function precisionOptions(): array
    {
        return collect(PlaceLocationPrecision::cases())
            ->mapWithKeys(static fn (PlaceLocationPrecision $precision): array => [$precision->value => $precision->label()])
            ->all();
    }

    public function submit(): void
    {
        $user = $this->requireUser();
        $this->authorize('create', PlaceSubmission::class);
        $submission = $this->submitPlace->handle(
            $user,
            $this->form->data($this->idempotencyKey, $user->locale),
        );

        session()->flash('place-submission-feedback', __('places.submissions.feedback.submitted'));
        $this->redirectRoute('places.submissions.show', ['placeSubmission' => $submission->stable_key]);
    }

    public function render(): View
    {
        return view('livewire.places.create-place-submission')
            ->layout('components.livewire-app-layout', [
                'title' => __('places.submissions.create.title'),
                'activeSection' => 'places',
            ]);
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();
        abort_unless($user instanceof User && $user->isActive() && $user->hasVerifiedEmail(), 403);

        return $user;
    }
}
