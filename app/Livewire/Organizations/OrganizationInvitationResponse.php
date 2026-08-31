<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Actions\RespondToOrganizationInvitation;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class OrganizationInvitationResponse extends Component
{
    #[Locked]
    public int $invitationId;

    public string $feedback = '';

    private AuthFactory $auth;

    public function boot(AuthFactory $auth): void
    {
        $this->auth = $auth;
    }

    public function mount(OrganizationInvitation $organizationInvitation): void
    {
        $user = $this->requireUser();
        $token = request()->query('token');
        abort_unless(
            is_string($token)
            && $token !== ''
            && $organizationInvitation->invited_user_id === $user->id
            && $organizationInvitation->tokenMatches($token),
            403,
        );
        $this->invitationId = $organizationInvitation->id;
        session()->put($this->tokenSessionKey(), Crypt::encryptString($token));
    }

    /** @return array<string, string> */
    public function invitation(): array
    {
        $invitation = $this->invitationModel()->load('organization:id,name,slug');

        return [
            'organization' => $invitation->organization->name,
            'role' => $invitation->role->label(),
            'expires_at' => $invitation->expires_at->isoFormat('LLL'),
        ];
    }

    public function respond(bool $accept, RespondToOrganizationInvitation $respond): void
    {
        $invitation = $respond->handle(
            $this->requireUser(),
            $this->invitationModel(),
            $this->invitationToken(),
            $accept,
        );
        session()->forget($this->tokenSessionKey());
        $this->feedback = $accept
            ? __('organizations.feedback.invitation_accepted')
            : __('organizations.feedback.invitation_declined');

        if ($accept) {
            $this->redirectRoute('organizations.show', $invitation->organization);
        }
    }

    public function render(): View
    {
        return view('livewire.organizations.organization-invitation-response', [
            'invitation' => $this->invitation(),
        ])->layout('components.livewire-app-layout', [
            'title' => __('organizations.pages.invitation.title'),
            'activeSection' => 'organizations',
        ]);
    }

    private function invitationModel(): OrganizationInvitation
    {
        $invitation = OrganizationInvitation::query()->findOrFail($this->invitationId);
        abort_unless($invitation->invited_user_id === $this->requireUser()->id, 403);

        return $invitation;
    }

    private function invitationToken(): string
    {
        $encryptedToken = session()->get($this->tokenSessionKey());
        abort_unless(is_string($encryptedToken) && $encryptedToken !== '', 403);

        try {
            $token = Crypt::decryptString($encryptedToken);
        } catch (DecryptException) {
            abort(403);
        }

        return $token;
    }

    private function tokenSessionKey(): string
    {
        return 'organization_invitations.'.$this->invitationId.'.token';
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();
        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }
}
