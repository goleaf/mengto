<?php

declare(strict_types=1);

namespace App\Livewire\Pets;

use App\Actions\ReviewPetProfileAccessRequest;
use App\Enums\PetProfileAccessRequestDecision;
use App\Enums\PetProfileAccessRequestStatus;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\User;
use App\Services\LocaleFormatter;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PetProfileAccessRequests extends Component
{
    #[Locked]
    public int $profileId = 0;

    public string $feedback = '';

    /** @var array<string, string> */
    public array $resolutionNotes = [];

    private Gate $gate;

    private ReviewPetProfileAccessRequest $reviewAction;

    private LocaleFormatter $formatter;

    public function boot(
        Gate $gate,
        ReviewPetProfileAccessRequest $reviewAction,
        LocaleFormatter $formatter,
    ): void {
        $this->gate = $gate;
        $this->reviewAction = $reviewAction;
        $this->formatter = $formatter;
    }

    public function mount(PetProfile $petProfile): void
    {
        $profile = PetProfile::query()
            ->select(['id', 'user_id', 'profile_key', 'name', 'status'])
            ->findOrFail($petProfile->id);
        $this->gate->authorize('manageManagers', $profile);
        $this->profileId = $profile->id;
    }

    /** @return array{name: string, profile_key: string} */
    #[Computed]
    public function pet(): array
    {
        $profile = $this->profileModel();

        return [
            'name' => $profile->name,
            'profile_key' => $profile->profile_key,
        ];
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function requests(): array
    {
        $this->gate->authorize('manageManagers', $this->profileModel());

        return PetProfileAccessRequest::query()
            ->select([
                'id',
                'request_key',
                'pet_profile_id',
                'requester_user_id',
                'requester_actor_key_snapshot',
                'request_type',
                'requested_role',
                'status',
                'evidence_summary',
                'temporary_access_ends_at',
                'created_at',
            ])
            ->where('pet_profile_id', $this->profileId)
            ->where('status', PetProfileAccessRequestStatus::Pending)
            ->with('requester:id,name')
            ->orderByDesc('id')
            ->limit(25)
            ->get()
            ->map(function (PetProfileAccessRequest $request): array {
                $requester = $request->requester;

                return [
                    'request_key' => $request->request_key,
                    'requester' => $requester instanceof User
                        ? $requester->name
                        : __('pet_profiles.access_requests.unavailable_requester'),
                    'type' => $request->request_type->label(),
                    'role' => $request->requested_role->label(),
                    'evidence' => $request->evidence_summary,
                    'temporary_ends_at' => $this->formatter->dateTime(
                        $request->temporary_access_ends_at,
                    ),
                    'submitted_at' => $this->formatter->dateTime($request->created_at),
                    'protected' => $request->request_type->requiresProtectedApproval(),
                ];
            })->all();
    }

    public function approve(string $requestKey): void
    {
        $request = $this->pendingRequest($requestKey);
        $this->reviewAction->handle(
            $request,
            PetProfileAccessRequestDecision::Approve,
            (string) ($this->resolutionNotes[$requestKey] ?? ''),
            'pet-access-approve:'.Str::uuid(),
        );
        $this->feedback = __('pet_profiles.feedback.access_request_approved');
        unset($this->resolutionNotes[$requestKey], $this->requests);
    }

    public function reject(string $requestKey): void
    {
        $request = $this->pendingRequest($requestKey);
        $this->reviewAction->handle(
            $request,
            PetProfileAccessRequestDecision::Reject,
            (string) ($this->resolutionNotes[$requestKey] ?? ''),
            'pet-access-reject:'.Str::uuid(),
        );
        $this->feedback = __('pet_profiles.feedback.access_request_rejected');
        unset($this->resolutionNotes[$requestKey], $this->requests);
    }

    public function render(): View
    {
        $pet = $this->pet();

        return view('livewire.pets.pet-profile-access-requests', ['pet' => $pet])
            ->layout('components.livewire-app-layout', [
                'title' => __('pet_profiles.access_requests.review_title', ['name' => $pet['name']]),
                'activeSection' => 'pets',
            ]);
    }

    private function pendingRequest(string $requestKey): PetProfileAccessRequest
    {
        $this->gate->authorize('manageManagers', $this->profileModel());

        return PetProfileAccessRequest::query()
            ->where('pet_profile_id', $this->profileId)
            ->where('status', PetProfileAccessRequestStatus::Pending)
            ->where('request_key', $requestKey)
            ->firstOrFail();
    }

    private function profileModel(): PetProfile
    {
        return PetProfile::query()
            ->select(['id', 'user_id', 'profile_key', 'name', 'status'])
            ->findOrFail($this->profileId);
    }
}
