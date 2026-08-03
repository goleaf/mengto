<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Actions\ApplyOrganizationRestriction;
use App\Actions\InviteOrganizationMember;
use App\Actions\RemoveOrganizationMember;
use App\Actions\SuspendOrganization;
use App\Enums\OrganizationRestrictionCapability;
use App\Enums\OrganizationRole;
use App\Livewire\Forms\OrganizationInvitationForm;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class OrganizationWorkspace extends Component
{
    #[Locked]
    public int $organizationId;

    public OrganizationInvitationForm $invitationForm;

    public string $memberRemovalReason = '';

    public string $restrictionCapability = 'create_invitations';

    public string $restrictionReason = '';

    public string $restrictionIdempotencyKey = '';

    public string $suspensionReason = '';

    public string $suspensionIdempotencyKey = '';

    public string $feedback = '';

    private AuthFactory $auth;

    private ProfilePresenter $profiles;

    public function boot(AuthFactory $auth, ProfilePresenter $profiles): void
    {
        $this->auth = $auth;
        $this->profiles = $profiles;
    }

    public function mount(Organization $organization): void
    {
        $this->organizationId = $organization->id;
        Gate::authorize('view', $organization);
        $this->resetInvitationForm();
        $this->restrictionIdempotencyKey = (string) Str::uuid();
        $this->suspensionIdempotencyKey = (string) Str::uuid();
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function workspace(): array
    {
        $user = $this->requireUser();
        $organization = $this->organizationModel();
        Gate::forUser($user)->authorize('view', $organization);
        $canManageMembers = Gate::forUser($user)->allows('manageMembers', $organization);
        $canManageRestrictions = Gate::forUser($user)
            ->allows('manageRestrictions', $organization);
        $relations = [
            'owner:id,name',
            'memberships' => function (Relation $relation) use ($canManageMembers): void {
                $relation->getQuery()
                    ->select([
                        'id',
                        'organization_id',
                        'user_id',
                        'role',
                        'status',
                        'joined_at',
                        'expires_at',
                        'removed_at',
                        'lock_version',
                    ])
                    ->with($canManageMembers ? 'user:id,name,email' : 'user:id,name')
                    ->orderBy('status')
                    ->orderBy('role')
                    ->orderBy('id')
                    ->limit(100);
            },
        ];

        if ($canManageRestrictions) {
            $relations['activeRestrictions'] = function (Relation $relation): void {
                $relation->getQuery()
                    ->select([
                        'id',
                        'organization_id',
                        'capability',
                        'reason_code',
                        'starts_at',
                        'ends_at',
                        'revoked_at',
                    ])
                    ->orderBy('capability')
                    ->limit(50);
            };
        }

        $organization->load($relations);

        return [
            'name' => $organization->name,
            'summary' => $organization->summary,
            'type' => $organization->type->label(),
            'status' => $organization->status->label(),
            'status_key' => $organization->status->value,
            'verification' => $organization->verification_status->label(),
            'owner' => $organization->owner->name,
            'public_region' => $organization->public_region,
            'can_manage_members' => $canManageMembers,
            'can_manage_restrictions' => $canManageRestrictions,
            'memberships' => $organization->memberships->map(
                static fn (OrganizationMembership $membership): array => [
                    'id' => $membership->id,
                    'name' => $membership->user->name,
                    'email' => $canManageMembers ? $membership->user->email : null,
                    'role' => $membership->role->label(),
                    'role_key' => $membership->role->value,
                    'status' => $membership->status->label(),
                    'status_key' => $membership->status->value,
                ],
            )->all(),
            'restrictions' => ($organization->relationLoaded('activeRestrictions')
                ? $organization->activeRestrictions
                : collect())->map(
                    static fn ($restriction): array => [
                        'id' => $restriction->id,
                        'capability' => $restriction->capability->label(),
                        'reason_code' => $restriction->reason_code,
                    ],
                )->all(),
        ];
    }

    /** @return array<string, string> */
    #[Computed]
    public function roleOptions(): array
    {
        return collect(OrganizationRole::assignableCases())
            ->mapWithKeys(static fn (OrganizationRole $role): array => [
                $role->value => $role->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function restrictionOptions(): array
    {
        return collect(OrganizationRestrictionCapability::operationalEventCapabilities())
            ->mapWithKeys(static fn (OrganizationRestrictionCapability $capability): array => [
                $capability->value => $capability->label(),
            ])
            ->all();
    }

    public function invite(InviteOrganizationMember $invite): void
    {
        $data = $this->invitationForm->data();
        $recipient = User::query()
            ->select(['id', 'actor_key', 'name', 'email', 'email_verified_at', 'status'])
            ->where('email', $data['email'])
            ->firstOrFail();
        $invitation = $invite->handle(
            $this->requireUser(),
            $this->organizationModel(),
            $recipient,
            $data['invitation'],
        );
        session()->now(
            $this->invitationUrlSessionKey(),
            Crypt::encryptString(
                $invitation->signedResponseUrl((string) $invitation->plainTextToken),
            ),
        );
        $this->feedback = __('organizations.feedback.invited');
        $this->resetInvitationForm();
        unset($this->workspace);
    }

    public function removeMember(int $membershipId, RemoveOrganizationMember $remove): void
    {
        $membership = OrganizationMembership::query()
            ->where('organization_id', $this->organizationId)
            ->findOrFail($membershipId);
        $remove->handle($this->requireUser(), $membership, $this->memberRemovalReason);
        $this->memberRemovalReason = '';
        $this->feedback = __('organizations.feedback.member_removed');
        unset($this->workspace);
    }

    public function restrict(ApplyOrganizationRestriction $apply): void
    {
        $capability = OrganizationRestrictionCapability::from($this->restrictionCapability);
        $apply->handle(
            $this->requireUser(),
            $this->organizationModel(),
            $capability,
            $this->restrictionReason,
            $this->restrictionIdempotencyKey,
        );
        $this->restrictionReason = '';
        $this->restrictionIdempotencyKey = (string) Str::uuid();
        $this->feedback = __('organizations.feedback.restricted');
        unset($this->workspace);
    }

    public function suspend(SuspendOrganization $suspend): void
    {
        $suspend->handle(
            $this->requireUser(),
            $this->organizationModel(),
            $this->suspensionReason,
            $this->suspensionIdempotencyKey,
        );
        $this->suspensionReason = '';
        $this->suspensionIdempotencyKey = (string) Str::uuid();
        $this->feedback = __('organizations.feedback.suspended');
        unset($this->workspace);
    }

    public function render(): View
    {
        return view('livewire.organizations.organization-workspace', [
            'invitationUrl' => $this->invitationUrl(),
        ])
            ->layout('components.livewire-app-layout', [
                'owner' => $this->profiles->owner(),
                'title' => __('organizations.pages.show.title'),
                'activeSection' => 'organizations',
            ]);
    }

    private function resetInvitationForm(): void
    {
        $this->invitationForm->reset();
        $this->invitationForm->expiresAt = now()->addWeek()->format('Y-m-d\TH:i');
        $this->invitationForm->idempotencyKey = (string) Str::uuid();
    }

    private function organizationModel(): Organization
    {
        return Organization::query()->findOrFail($this->organizationId);
    }

    private function invitationUrlSessionKey(): string
    {
        return 'organizations.'.$this->organizationId.'.invitation_url';
    }

    private function invitationUrl(): string
    {
        $encryptedUrl = session()->get($this->invitationUrlSessionKey());

        if (! is_string($encryptedUrl) || $encryptedUrl === '') {
            return '';
        }

        try {
            return Crypt::decryptString($encryptedUrl);
        } catch (DecryptException) {
            session()->forget($this->invitationUrlSessionKey());

            return '';
        }
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();
        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }
}
