<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Actions\CreateOrganization;
use App\Enums\OrganizationType;
use App\Livewire\Forms\OrganizationForm;
use App\Models\Organization;
use App\Models\User;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

final class OrganizationDirectory extends Component
{
    use WithPagination;

    public OrganizationForm $form;

    public string $feedback = '';

    private AuthFactory $auth;

    private ProfilePresenter $profiles;

    public function boot(AuthFactory $auth, ProfilePresenter $profiles): void
    {
        $this->auth = $auth;
        $this->profiles = $profiles;
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', Organization::class);
        $this->resetCreationForm();
    }

    /** @return LengthAwarePaginator<int, array<string, mixed>> */
    #[Computed]
    public function organizations(): LengthAwarePaginator
    {
        $user = $this->requireUser();

        return Organization::query()
            ->accessibleTo($user)
            ->select([
                'id',
                'owner_user_id',
                'stable_key',
                'slug',
                'name',
                'summary',
                'type',
                'status',
                'verification_status',
                'default_locale',
                'public_region',
                'archived_at',
                'created_at',
            ])
            ->withCount(['activeMemberships'])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12)
            ->through($this->presentOrganization(...));
    }

    /** @return array<string, string> */
    #[Computed]
    public function typeOptions(): array
    {
        return collect(OrganizationType::cases())
            ->reject(static fn (OrganizationType $type): bool => $type === OrganizationType::Platform)
            ->mapWithKeys(static fn (OrganizationType $type): array => [
                $type->value => $type->label(),
            ])
            ->all();
    }

    public function create(CreateOrganization $create): void
    {
        $organization = $create->handle($this->requireUser(), $this->form->data());
        $this->feedback = __('organizations.feedback.created');
        $this->resetCreationForm();
        unset($this->organizations);
        $this->redirectRoute('organizations.show', $organization);
    }

    public function render(): View
    {
        return view('livewire.organizations.organization-directory')
            ->layout('components.livewire-app-layout', [
                'owner' => $this->profiles->owner(),
                'title' => __('organizations.pages.index.title'),
                'activeSection' => 'organizations',
            ]);
    }

    private function resetCreationForm(): void
    {
        $this->form->reset();
        $this->form->defaultLocale = $this->requireUser()->locale;
        $this->form->idempotencyKey = (string) Str::uuid();
    }

    /** @return array<string, mixed> */
    private function presentOrganization(Organization $organization): array
    {
        return [
            'id' => $organization->id,
            'name' => $organization->name,
            'summary' => $organization->summary,
            'type' => $organization->type->label(),
            'status' => $organization->status->label(),
            'verification' => $organization->verification_status->label(),
            'member_count' => $organization->active_memberships_count,
            'url' => route('organizations.show', $organization),
        ];
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();
        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }
}
