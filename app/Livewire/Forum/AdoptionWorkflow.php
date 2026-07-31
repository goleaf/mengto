<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\CloseAdoptionCase;
use App\Actions\SubmitAdoptionApplication;
use App\Actions\TransitionAdoptionApplication;
use App\Enums\AdoptionApplicationStatus;
use App\Enums\AdoptionPlacementType;
use App\Livewire\Forms\AdoptionApplicationForm;
use App\Models\AdoptionApplication;
use App\Models\AdoptionCase;
use App\Models\User;
use App\Services\LocaleFormatter;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class AdoptionWorkflow extends Component
{
    public AdoptionApplicationForm $form;

    #[Locked]
    public int $listingId;

    #[Locked]
    public ?int $caseId = null;

    #[Locked]
    public string $idempotencyKey = '';

    #[Locked]
    public ?int $selectedApplicationId = null;

    #[Locked]
    public int $selectedApplicationVersion = 0;

    #[Locked]
    public int $caseVersion = 0;

    public string $targetStatus = 'screening';

    public string $feedback = '';

    private AuthFactory $auth;

    private CloseAdoptionCase $closeCaseAction;

    private Gate $gate;

    private LocaleFormatter $formatter;

    private SubmitAdoptionApplication $submitAction;

    private TransitionAdoptionApplication $transitionAction;

    public function boot(
        AuthFactory $auth,
        Gate $gate,
        LocaleFormatter $formatter,
        SubmitAdoptionApplication $submitAction,
        TransitionAdoptionApplication $transitionAction,
        CloseAdoptionCase $closeCaseAction,
    ): void {
        $this->auth = $auth;
        $this->gate = $gate;
        $this->formatter = $formatter;
        $this->submitAction = $submitAction;
        $this->transitionAction = $transitionAction;
        $this->closeCaseAction = $closeCaseAction;
    }

    public function mount(int $listingId): void
    {
        $this->listingId = $listingId;
        $case = AdoptionCase::query()
            ->select(['id', 'listing_id', 'lock_version'])
            ->where('listing_id', $listingId)
            ->first();
        $this->caseId = $case?->id;
        $this->caseVersion = $case === null ? 0 : $case->lock_version;
        $this->idempotencyKey = (string) str()->uuid();
    }

    /** @return array<string, array<int, string>|int|string|bool|null> */
    #[Computed]
    public function caseData(): array
    {
        $case = $this->case();
        $this->gate->forUser($this->user())->authorize('view', $case);
        $providerIdentityStatus = $case->effectiveProviderIdentityStatus();

        return [
            'number' => $case->case_number,
            'animal_name' => $case->animal_name,
            'status' => $case->status->value,
            'status_label' => $case->status->label(),
            'provider_type' => $case->provider_type->label(),
            'provider_identity_status' => $providerIdentityStatus->value,
            'provider_identity_label' => $providerIdentityStatus->label(),
            'provider_verified' => $providerIdentityStatus->isVerified(),
            'age' => $case->age_description,
            'sex' => $case->sex,
            'sterilization' => __("adoption.health_state.{$case->sterilization_status}"),
            'vaccination' => __("adoption.health_state.{$case->vaccination_status}"),
            'microchip' => __("adoption.health_state.{$case->microchip_status}"),
            'location' => $case->public_location,
            'health' => $case->health_summary,
            'behavior' => $case->behavior_summary,
            'compatibility' => $case->compatibility_summary,
            'requirements' => $case->special_requirements,
            'fee' => $this->formatter->currency($case->adoption_fee_minor / 100, $case->currency),
            'fee_explanation' => $case->fee_explanation ?? __('adoption.default_fee_explanation'),
            'transport' => $case->transport_options ?? [],
            'accepts_applications' => $case->status->acceptsApplications(),
        ];
    }

    /** @return list<array<string, int|string>> */
    #[Computed]
    public function applications(): array
    {
        $user = $this->user();

        if (! $user instanceof User || $this->caseId === null) {
            return [];
        }

        $case = $this->case();
        $canManage = $this->gate->forUser($user)->allows('manage', $case);

        return AdoptionApplication::query()
            ->select([
                'id',
                'adoption_case_id',
                'applicant_user_id',
                'placement_type',
                'status',
                'identity_status',
                'message',
                'lock_version',
                'submitted_at',
            ])
            ->with('applicant:id,name')
            ->where('adoption_case_id', $case->id)
            ->when(
                ! $canManage,
                fn (Builder $query): Builder => $query->where('applicant_user_id', $user->id),
            )
            ->latest('submitted_at')
            ->limit(50)
            ->get()
            ->map(fn (AdoptionApplication $application): array => [
                'id' => $application->id,
                'applicant' => $application->applicant->name,
                'placement' => $application->placement_type->label(),
                'status' => $application->status->value,
                'status_label' => $application->status->label(),
                'identity_status' => __("adoption.identity_status.{$application->identity_status}"),
                'message' => $application->message,
                'version' => $application->lock_version,
                'submitted' => $this->formatter->dateTime($application->submitted_at),
            ])
            ->all();
    }

    /** @return array<string, string>|null */
    #[Computed]
    public function selectedApplication(): ?array
    {
        if ($this->selectedApplicationId === null) {
            return null;
        }

        $application = $this->application($this->selectedApplicationId);
        $this->gate->forUser($this->requireUser())->authorize('view', $application);

        return $application->private_profile;
    }

    /** @return array<string, string> */
    #[Computed]
    public function transitionOptions(): array
    {
        if ($this->selectedApplicationId === null) {
            return [];
        }

        $application = $this->application($this->selectedApplicationId);
        $user = $this->requireUser();
        $options = $application->status->allowedTransitions();

        if (
            $application->applicant_user_id === $user->id
            && ! $user->isAdministrator()
            && $application->adoptionCase->listing->owner_key !== $user->actor_key
        ) {
            $options = array_values(array_filter(
                $options,
                static fn (AdoptionApplicationStatus $status): bool => $status === AdoptionApplicationStatus::Withdrawn,
            ));
        }

        return collect($options)
            ->mapWithKeys(static fn (AdoptionApplicationStatus $status): array => [
                $status->value => $status->label(),
            ])
            ->all();
    }

    #[Computed]
    public function canManage(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && $this->gate->forUser($user)->allows('manage', $this->case());
    }

    #[Computed]
    public function canApply(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && $this->gate->forUser($user)->allows('apply', $this->case())
            && $this->applications() === [];
    }

    /** @return array<string, string> */
    #[Computed]
    public function placementOptions(): array
    {
        return collect(AdoptionPlacementType::cases())
            ->mapWithKeys(static fn (AdoptionPlacementType $type): array => [
                $type->value => $type->label(),
            ])
            ->all();
    }

    public function submit(): void
    {
        $user = $this->requireUser();
        $application = $this->submitAction->handle(
            $user,
            $this->case(),
            $this->form->data(),
            $this->idempotencyKey,
        );

        $this->selectedApplicationId = $application->id;
        $this->selectedApplicationVersion = $application->lock_version;
        $this->idempotencyKey = (string) str()->uuid();
        $this->form->reset();
        $this->feedback = __('adoption.feedback.application_submitted');
        unset($this->applications, $this->selectedApplication, $this->transitionOptions);
    }

    public function selectApplication(int $applicationId): void
    {
        $application = $this->application($applicationId);
        $this->gate->forUser($this->requireUser())->authorize('view', $application);
        $this->selectedApplicationId = $application->id;
        $this->selectedApplicationVersion = $application->lock_version;
        unset($this->selectedApplication, $this->transitionOptions);
        $this->targetStatus = array_key_first($this->transitionOptions())
            ?? AdoptionApplicationStatus::Closed->value;
    }

    public function updateApplicationStatus(): void
    {
        $validated = $this->validate([
            'targetStatus' => ['required', Rule::enum(AdoptionApplicationStatus::class)],
        ]);
        $application = $this->application((int) $this->selectedApplicationId);
        $updated = $this->transitionAction->handle(
            $this->requireUser(),
            $application,
            AdoptionApplicationStatus::from((string) $validated['targetStatus']),
            $this->selectedApplicationVersion,
        );

        $this->selectedApplicationVersion = $updated->lock_version;
        $this->caseVersion = $updated->adoptionCase()->value('lock_version') ?? $this->caseVersion;
        $this->feedback = __('adoption.feedback.application_updated');
        unset(
            $this->applications,
            $this->caseData,
            $this->selectedApplication,
            $this->transitionOptions,
        );
    }

    public function closeCase(): void
    {
        $case = $this->closeCaseAction->handle(
            $this->requireUser(),
            $this->case(),
            $this->caseVersion,
        );
        $this->caseVersion = $case->lock_version;
        $this->feedback = __('adoption.feedback.case_closed');
        unset($this->applications, $this->caseData);
    }

    public function render(): View
    {
        return view('livewire.forum.adoption-workflow', [
            'isAuthenticated' => $this->user() instanceof User,
        ]);
    }

    private function case(): AdoptionCase
    {
        abort_if($this->caseId === null, 404);

        return AdoptionCase::query()
            ->with('listing:id,owner_key,status,moderation_status')
            ->findOrFail($this->caseId);
    }

    private function application(int $id): AdoptionApplication
    {
        abort_if($this->caseId === null, 404);

        return AdoptionApplication::query()
            ->with('adoptionCase.listing:id,owner_key,status,moderation_status')
            ->where('adoption_case_id', $this->caseId)
            ->findOrFail($id);
    }

    private function requireUser(): User
    {
        $user = $this->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    private function user(): ?User
    {
        $user = $this->auth->guard()->user();

        return $user instanceof User ? $user : null;
    }
}
