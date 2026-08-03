<section
    class="grid gap-6 border-t border-border-subtle pt-6"
    aria-labelledby="group-management-heading"
    @if (! $this->workspace['authorized']) hidden aria-hidden="true" @endif
>
    @if ($this->workspace['authorized'])
        <header>
            <p class="forum-header__eyebrow">{{ __('forum_groups.labels.managed') }}</p>
            <h2 id="group-management-heading">{{ __('forum_groups.page.manage_heading') }}</h2>
            <p>{{ __('forum_groups.notices.management_limit') }}</p>
        </header>

        @if ($feedback !== '')
            <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
                {{ $feedback }}
            </p>
        @endif

        @if ($errors->any())
            <x-forum-error-summary
                :messages="$errors->getMessages()"
                :heading="__('forum_groups.validation.summary')"
            />
        @endif

        @if ($this->workspace['can_invite'])
            <details class="forum-form">
                <summary class="forum-button min-h-11">
                    <x-ui-icon name="user-plus" />
                    {{ __('forum_groups.page.invite_heading') }}
                </summary>
                <form wire:submit="invite" class="mt-4 grid gap-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="forum-form__field">
                            <span>{{ __('forum_groups.fields.invite_email') }}</span>
                            <input type="email" wire:model="inviteEmail" maxlength="255" autocomplete="off">
                            @error('inviteEmail') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_groups.fields.invite_role') }}</span>
                            <select wire:model="inviteRole">
                                @foreach ($this->inviteRoleOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <label class="forum-form__field">
                        <span>{{ __('forum_groups.fields.invite_message') }}</span>
                        <textarea wire:model="inviteMessage" rows="3" maxlength="1000"></textarea>
                        @error('inviteMessage') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <button
                        class="forum-button forum-button--primary min-h-11 justify-self-start"
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="invite"
                    >
                        <x-ui-icon name="send" />
                        {{ __('forum_groups.actions.invite') }}
                    </button>
                </form>
            </details>
        @endif

        <section aria-labelledby="group-memberships-heading">
            <h3 id="group-memberships-heading">{{ __('forum_groups.page.members_heading') }}</h3>
            <div class="mt-3 divide-y divide-border-subtle border-y border-border-subtle">
                @forelse ($this->workspace['memberships'] as $membership)
                    <article class="grid gap-4 py-4" wire:key="managed-membership-{{ $membership['id'] }}">
                        <header class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h4>{{ $membership['name'] }}</h4>
                                <p>{{ $membership['email'] }}</p>
                            </div>
                            <x-status-badge :label="$membership['state']" icon="user-round-check" />
                        </header>

                        <dl class="grid gap-2 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="font-semibold">{{ __('forum_groups.fields.invite_role') }}</dt>
                                <dd>{{ $membership['role'] }}</dd>
                            </div>
                            @if ($membership['restriction_reason'])
                                <div>
                                    <dt class="font-semibold">{{ __('forum_groups.fields.member_reason') }}</dt>
                                    <dd>{{ $membership['restriction_reason'] }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if ($membership['answers'] !== [])
                            <div>
                                <h5>{{ __('forum_groups.fields.answers') }}</h5>
                                <ul class="mt-2 list-disc space-y-1 ps-5">
                                    @foreach ($membership['answers'] as $answer)
                                        <li>{{ $answer }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($membership['state_key'] === 'pending' && $this->workspace['can_review'])
                            <form class="grid gap-3">
                                <label class="forum-form__field">
                                    <span>{{ __('forum_groups.fields.review_reason') }}</span>
                                    <textarea wire:model="reviewReason" rows="3" minlength="3" maxlength="1000"></textarea>
                                    @error('reviewReason') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        class="forum-button forum-button--primary min-h-11"
                                        type="button"
                                        wire:click="review({{ $membership['id'] }}, true, {{ $membership['lock_version'] }})"
                                        wire:loading.attr="disabled"
                                        wire:target="review"
                                    >
                                        <x-ui-icon name="check" />
                                        {{ __('forum_groups.actions.approve') }}
                                    </button>
                                    <button
                                        class="forum-button min-h-11"
                                        type="button"
                                        wire:click="review({{ $membership['id'] }}, false, {{ $membership['lock_version'] }})"
                                        wire:loading.attr="disabled"
                                        wire:target="review"
                                    >
                                        <x-ui-icon name="x" />
                                        {{ __('forum_groups.actions.reject') }}
                                    </button>
                                </div>
                            </form>
                        @elseif ($membership['state_key'] === 'active' && $membership['role_key'] !== 'owner')
                            <div class="grid gap-4">
                                <label class="forum-form__field max-w-md">
                                    <span>{{ __('forum_groups.actions.change_role') }}</span>
                                    <select
                                        wire:change="changeRole({{ $membership['id'] }}, $event.target.value, {{ $membership['lock_version'] }})"
                                    >
                                        @foreach ($this->roleOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($membership['role_key'] === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="forum-form__field">
                                    <span>{{ __('forum_groups.fields.member_reason') }}</span>
                                    <textarea wire:model="memberReason" rows="3" minlength="3" maxlength="2000"></textarea>
                                    @error('memberReason') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        class="forum-button min-h-11"
                                        type="button"
                                        wire:click="restrict({{ $membership['id'] }}, false, {{ $membership['lock_version'] }})"
                                        wire:confirm="{{ __('forum_groups.actions.remove') }}"
                                        wire:loading.attr="disabled"
                                        wire:target="restrict"
                                    >
                                        <x-ui-icon name="user-minus" />
                                        {{ __('forum_groups.actions.remove') }}
                                    </button>
                                    <button
                                        class="forum-button forum-button--danger min-h-11"
                                        type="button"
                                        wire:click="restrict({{ $membership['id'] }}, true, {{ $membership['lock_version'] }})"
                                        wire:confirm="{{ __('forum_groups.actions.ban') }}"
                                        wire:loading.attr="disabled"
                                        wire:target="restrict"
                                    >
                                        <x-ui-icon name="ban" />
                                        {{ __('forum_groups.actions.ban') }}
                                    </button>
                                </div>
                            </div>

                            @if ($this->workspace['can_transfer'])
                                <form class="grid gap-3 border-s-4 border-status-warning ps-4">
                                    <label class="forum-form__field">
                                        <span>{{ __('forum_groups.fields.transfer_reason') }}</span>
                                        <textarea wire:model="transferReason" rows="3" minlength="3" maxlength="2000"></textarea>
                                        @error('transferReason') <small role="alert">{{ $message }}</small> @enderror
                                    </label>
                                    <button
                                        class="forum-button min-h-11 justify-self-start"
                                        type="button"
                                        wire:click="transfer({{ $membership['id'] }}, {{ $this->workspace['lock_version'] }})"
                                        wire:confirm="{{ __('forum_groups.actions.transfer') }}"
                                        wire:loading.attr="disabled"
                                        wire:target="transfer"
                                    >
                                        <x-ui-icon name="arrow-left-right" />
                                        {{ __('forum_groups.actions.transfer') }}
                                    </button>
                                </form>
                            @endif
                        @endif
                    </article>
                @empty
                    <p class="py-4">{{ __('forum_groups.empty.members') }}</p>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="group-pending-invitations-heading">
            <h3 id="group-pending-invitations-heading">{{ __('forum_groups.page.pending_invitations_heading') }}</h3>
            <div class="mt-3 divide-y divide-border-subtle border-y border-border-subtle">
                @forelse ($this->workspace['invitations'] as $invitation)
                    <article
                        class="flex flex-wrap items-center justify-between gap-3 py-4"
                        wire:key="managed-invitation-{{ $invitation['id'] }}"
                    >
                        <div>
                            <h4>{{ $invitation['name'] }}</h4>
                            <p>{{ $invitation['email'] }} · {{ $invitation['role'] }}</p>
                            <p>{{ __('forum_groups.labels.expires', ['date' => $invitation['expires_at']]) }}</p>
                        </div>
                        <button
                            class="forum-button min-h-11"
                            type="button"
                            wire:click="revoke({{ $invitation['id'] }})"
                            wire:confirm="{{ __('forum_groups.actions.revoke_invitation') }}"
                            wire:loading.attr="disabled"
                            wire:target="revoke"
                        >
                            <x-ui-icon name="x" />
                            {{ __('forum_groups.actions.revoke_invitation') }}
                        </button>
                    </article>
                @empty
                    <p class="py-4">{{ __('forum_groups.empty.pending_invitations') }}</p>
                @endforelse
            </div>
        </section>

        @if ($this->workspace['can_close'] || $this->workspace['can_archive'])
            <section class="forum-form" aria-labelledby="group-lifecycle-heading">
                <h3 id="group-lifecycle-heading">{{ __('forum_groups.page.lifecycle_heading') }}</h3>
                <p>{{ __('forum_groups.notices.lifecycle_audit') }}</p>
                <label class="forum-form__field">
                    <span>{{ __('forum_groups.fields.lifecycle_reason') }}</span>
                    <textarea wire:model="lifecycleReason" rows="3" minlength="3" maxlength="2000"></textarea>
                    @error('lifecycleReason') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <div class="flex flex-wrap gap-2">
                    @if ($this->workspace['can_close'] && $this->workspace['status_key'] === 'active')
                        <button
                            class="forum-button min-h-11"
                            type="button"
                            wire:click="changeStatus('closed', {{ $this->workspace['lock_version'] }})"
                            wire:confirm="{{ __('forum_groups.actions.close') }}"
                            wire:loading.attr="disabled"
                            wire:target="transition"
                        >
                            <x-ui-icon name="lock" />
                            {{ __('forum_groups.actions.close') }}
                        </button>
                    @elseif ($this->workspace['can_close'] && $this->workspace['status_key'] === 'closed')
                        <button
                            class="forum-button min-h-11"
                            type="button"
                            wire:click="changeStatus('active', {{ $this->workspace['lock_version'] }})"
                            wire:confirm="{{ __('forum_groups.actions.reopen') }}"
                            wire:loading.attr="disabled"
                            wire:target="transition"
                        >
                            <x-ui-icon name="lock-open" />
                            {{ __('forum_groups.actions.reopen') }}
                        </button>
                    @endif
                    @if ($this->workspace['can_archive'] && $this->workspace['status_key'] !== 'archived')
                        <button
                            class="forum-button forum-button--danger min-h-11"
                            type="button"
                            wire:click="changeStatus('archived', {{ $this->workspace['lock_version'] }})"
                            wire:confirm="{{ __('forum_groups.actions.archive') }}"
                            wire:loading.attr="disabled"
                            wire:target="transition"
                        >
                            <x-ui-icon name="archive" />
                            {{ __('forum_groups.actions.archive') }}
                        </button>
                    @endif
                </div>
            </section>
        @endif

        <section aria-labelledby="group-audit-heading">
            <h3 id="group-audit-heading">{{ __('forum_groups.page.audit_heading') }}</h3>
            <ol class="mt-3 divide-y divide-border-subtle border-y border-border-subtle">
                @forelse ($this->workspace['events'] as $event)
                    <li class="grid gap-1 py-3" wire:key="group-audit-event-{{ $event['id'] }}">
                        <strong>{{ $event['summary'] }}</strong>
                        <span>{{ $event['created_at'] }}</span>
                        @if ($event['actor'])
                            <span>{{ __('forum_groups.labels.actor', ['name' => $event['actor']]) }}</span>
                        @endif
                        @if ($event['subject'])
                            <span>{{ __('forum_groups.labels.subject', ['name' => $event['subject']]) }}</span>
                        @endif
                        <span>{{ __('forum_groups.labels.reason_code', ['code' => $event['reason_code']]) }}</span>
                    </li>
                @empty
                    <li class="py-4">{{ __('forum_groups.empty.events') }}</li>
                @endforelse
            </ol>
        </section>
    @endif
</section>
