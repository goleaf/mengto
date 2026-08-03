<x-page-stack data-section="organization-workspace">
    <x-page-header
        :eyebrow="__('organizations.pages.show.eyebrow')"
        :title="$this->workspace['name']"
        :description="$this->workspace['summary'] ?? __('organizations.pages.show.description')"
        heading-id="organization-workspace-heading"
        :action-label="__('organizations.actions.back_to_directory')"
        action-icon="arrow-left"
        :action-href="route('organizations.index')"
    >
        <x-slot:meta>
            <x-status-badge :label="$this->workspace['status']" icon="building-2" />
        </x-slot:meta>
    </x-page-header>

    @if ($feedback !== '')
        <x-flash-feedback :message="$feedback" />
    @endif

    @if ($errors->any())
        <x-forum-error-summary
            :messages="$errors->getMessages()"
            :heading="__('organizations.validation.summary')"
        />
    @endif

    <x-content-panel
        eyebrow="{{ __('organizations.pages.show.identity_eyebrow') }}"
        title="{{ __('organizations.pages.show.identity_title') }}"
    >
        <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div><dt class="font-semibold">{{ __('organizations.fields.type') }}</dt><dd>{{ $this->workspace['type'] }}</dd></div>
            <div><dt class="font-semibold">{{ __('organizations.fields.verification') }}</dt><dd>{{ $this->workspace['verification'] }}</dd></div>
            <div><dt class="font-semibold">{{ __('organizations.fields.owner') }}</dt><dd>{{ $this->workspace['owner'] }}</dd></div>
            <div><dt class="font-semibold">{{ __('organizations.fields.public_region') }}</dt><dd>{{ $this->workspace['public_region'] ?? __('organizations.labels.not_provided') }}</dd></div>
        </dl>
    </x-content-panel>

    @if ($this->workspace['can_manage_members'])
        <x-content-panel
            eyebrow="{{ __('organizations.pages.show.members_eyebrow') }}"
            title="{{ __('organizations.pages.show.invite_title') }}"
        >
            <form wire:submit="invite" class="mt-5 grid gap-5" novalidate>
                <div class="grid min-w-0 gap-5 md:grid-cols-3">
                    <label class="forum-form__field md:col-span-2" for="organization-invite-email">
                        <span>{{ __('organizations.fields.invite_email') }}</span>
                        <input id="organization-invite-email" type="email" wire:model="invitationForm.email" maxlength="255" required>
                    </label>
                    <label class="forum-form__field" for="organization-invite-role">
                        <span>{{ __('organizations.fields.role') }}</span>
                        <select id="organization-invite-role" wire:model="invitationForm.role">
                            @forelse ($this->roleOptions as $value => $label)
                                <option wire:key="organization-role-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option value="member">{{ __('organizations.roles.member') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="forum-form__field md:col-span-2" for="organization-invite-expiry">
                        <span>{{ __('organizations.fields.expires_at') }}</span>
                        <input id="organization-invite-expiry" type="datetime-local" wire:model="invitationForm.expiresAt" required>
                    </label>
                </div>
                <button class="forum-button forum-button--primary min-h-11 justify-self-start" type="submit" wire:loading.attr="disabled" wire:target="invite">
                    <x-ui-icon name="send" />
                    {{ __('organizations.actions.invite') }}
                </button>
            </form>

            @if ($invitationUrl !== '')
                <div class="mt-5 border-s-4 border-status-success py-3 ps-4" role="status">
                    <p class="font-semibold">{{ __('organizations.labels.invitation_link') }}</p>
                    <a class="break-all underline" href="{{ $invitationUrl }}">{{ $invitationUrl }}</a>
                </div>
            @endif

        </x-content-panel>
    @endif

    <section aria-labelledby="organization-members-heading">
        <h2 id="organization-members-heading">{{ __('organizations.pages.show.members_title') }}</h2>
        <div class="mt-4 divide-y divide-border-subtle border-y border-border-subtle">
            @forelse ($this->workspace['memberships'] as $membership)
                <article class="grid gap-4 py-4" wire:key="organization-membership-{{ $membership['id'] }}">
                    <header class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3>{{ $membership['name'] }}</h3>
                            @if ($membership['email'])
                                <p>{{ $membership['email'] }}</p>
                            @endif
                        </div>
                        <x-status-badge :label="$membership['status']" icon="user-round-check" />
                    </header>
                    <p>{{ $membership['role'] }}</p>

                    @if ($this->workspace['can_manage_members'] && $membership['status_key'] === 'active' && $membership['role_key'] !== 'owner')
                        <div class="grid gap-3">
                            <label class="forum-form__field" for="organization-removal-reason-{{ $membership['id'] }}">
                                <span>{{ __('organizations.fields.removal_reason') }}</span>
                                <input id="organization-removal-reason-{{ $membership['id'] }}" type="text" wire:model="memberRemovalReason" maxlength="120">
                            </label>
                            <button
                                class="forum-button min-h-11 justify-self-start"
                                type="button"
                                wire:click="removeMember({{ $membership['id'] }})"
                                wire:confirm="{{ __('organizations.actions.remove_confirmation') }}"
                                wire:loading.attr="disabled"
                                wire:target="removeMember"
                            >
                                <x-ui-icon name="user-minus" />
                                {{ __('organizations.actions.remove_member') }}
                            </button>
                        </div>
                    @endif
                </article>
            @empty
                <p class="py-5">{{ __('organizations.empty.members') }}</p>
            @endforelse
        </div>
    </section>

    @if ($this->workspace['can_manage_restrictions'])
        <x-content-panel
            eyebrow="{{ __('organizations.pages.show.safety_eyebrow') }}"
            title="{{ __('organizations.pages.show.restrictions_title') }}"
        >
            <form wire:submit="restrict" class="mt-5 grid gap-4">
                <label class="forum-form__field" for="organization-restriction-capability">
                    <span>{{ __('organizations.fields.capability') }}</span>
                    <select id="organization-restriction-capability" wire:model="restrictionCapability">
                        @forelse ($this->restrictionOptions as $value => $label)
                            <option wire:key="organization-capability-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="create_invitations">{{ __('organizations.restriction_capabilities.create_invitations') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="organization-restriction-reason">
                    <span>{{ __('organizations.fields.reason_code') }}</span>
                    <input id="organization-restriction-reason" type="text" wire:model="restrictionReason" maxlength="120" required>
                </label>
                <button class="forum-button min-h-11 justify-self-start" type="submit" wire:loading.attr="disabled" wire:target="restrict">
                    <x-ui-icon name="shield-alert" />
                    {{ __('organizations.actions.apply_restriction') }}
                </button>
            </form>

            <ul class="mt-5 grid gap-3">
                @forelse ($this->workspace['restrictions'] as $restriction)
                    <li class="border-s-4 border-status-warning py-2 ps-4" wire:key="organization-restriction-{{ $restriction['id'] }}">
                        <strong>{{ $restriction['capability'] }}</strong>
                        <span>{{ __('organizations.labels.reason', ['reason' => $restriction['reason_code']]) }}</span>
                    </li>
                @empty
                    <li>{{ __('organizations.empty.restrictions') }}</li>
                @endforelse
            </ul>

            <form wire:submit="suspend" class="mt-6 grid gap-4 border-t border-border-subtle pt-5">
                <label class="forum-form__field" for="organization-suspension-reason">
                    <span>{{ __('organizations.fields.suspension_reason') }}</span>
                    <input id="organization-suspension-reason" type="text" wire:model="suspensionReason" maxlength="120" required>
                </label>
                <button
                    class="forum-button forum-button--danger min-h-11 justify-self-start"
                    type="submit"
                    wire:confirm="{{ __('organizations.actions.suspend_confirmation') }}"
                    wire:loading.attr="disabled"
                    wire:target="suspend"
                >
                    <x-ui-icon name="ban" />
                    {{ __('organizations.actions.suspend') }}
                </button>
            </form>
        </x-content-panel>
    @endif
</x-page-stack>
