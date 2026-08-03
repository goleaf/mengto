<section class="grid gap-6" aria-labelledby="persistent-group-heading">
    <x-page-header
        :eyebrow="$this->group['visibility'].' · '.$this->group['status']"
        :title="$this->group['name']"
        :description="$this->group['description']"
        heading-id="persistent-group-heading"
        data-section="forum-group-workspace-header"
    />

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

    <dl class="grid gap-3 border-y border-border-subtle py-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <dt class="font-semibold">{{ __('forum_groups.roles.owner') }}</dt>
            <dd>{{ $this->group['owner_name'] }}</dd>
        </div>
        <div>
            <dt class="font-semibold">{{ __('forum_groups.fields.language') }}</dt>
            <dd>{{ $this->group['language'] }}</dd>
        </div>
        <div>
            <dt class="font-semibold">{{ __('forum_groups.membership_states.active') }}</dt>
            <dd>{{ __('forum_groups.labels.members', ['count' => $this->group['member_count']]) }}</dd>
        </div>
        @if ($this->group['location_scope'])
            <div>
                <dt class="font-semibold">{{ __('forum_groups.fields.location_scope') }}</dt>
                <dd>{{ $this->group['location_scope'] }}</dd>
            </div>
        @endif
    </dl>

    @if ($this->group['taxa'] !== [])
        <section aria-labelledby="group-species-heading">
            <h2 id="group-species-heading">{{ __('forum_groups.labels.species') }}</h2>
            <ul class="mt-2 flex flex-wrap gap-2">
                @foreach ($this->group['taxa'] as $taxon)
                    <li><x-status-badge :label="$taxon['scientific_name']" icon="paw-print" /></li>
                @endforeach
            </ul>
        </section>
    @endif

    <section aria-labelledby="group-rules-heading">
        <h2 id="group-rules-heading">{{ __('forum_groups.fields.rules') }}</h2>
        <ul class="mt-2 list-disc ps-5">
            @foreach ($this->group['rules'] as $rule)
                <li>{{ $rule }}</li>
            @endforeach
        </ul>
    </section>

    <section class="forum-form" aria-labelledby="group-participation-profile-heading">
        <h2 id="group-participation-profile-heading">{{ __('forum_groups.fields.participation_profile') }}</h2>
        <label class="forum-form__field">
            <span>{{ __('forum_groups.fields.participation_profile') }}</span>
            <select wire:model.live="selectedActorKey">
                @forelse ($this->actorOptions as $actorKey => $actorLabel)
                    <option value="{{ $actorKey }}">{{ $actorLabel }}</option>
                @empty
                    <option value="">{{ __('forum_groups.empty.participation_profiles') }}</option>
                @endforelse
            </select>
        </label>
        <p>{{ __('forum_groups.notices.real_account_audit') }}</p>
    </section>

    @if ($this->group['invitation_id'])
        <section class="forum-form" aria-labelledby="private-group-invitation-heading">
            <h2 id="private-group-invitation-heading">{{ __('forum_groups.page.invitations_heading') }}</h2>
            <p>{{ $this->group['invitation_role'] }}</p>
            @if ($this->group['invitation_message'])
                <p>{{ $this->group['invitation_message'] }}</p>
            @endif
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="forum-button forum-button--primary min-h-11"
                    wire:click="respondToInvitation({{ $this->group['invitation_id'] }}, true)"
                    wire:loading.attr="disabled"
                    wire:target="respondToInvitation"
                >
                    <x-lucide-check aria-hidden="true" />
                    {{ __('forum_groups.actions.accept_invitation') }}
                </button>
                <button
                    type="button"
                    class="forum-button min-h-11"
                    wire:click="respondToInvitation({{ $this->group['invitation_id'] }}, false)"
                    wire:loading.attr="disabled"
                    wire:target="respondToInvitation"
                >
                    <x-lucide-x aria-hidden="true" />
                    {{ __('forum_groups.actions.decline_invitation') }}
                </button>
            </div>
        </section>
    @elseif ($this->group['can_request'])
        <form wire:submit="requestMembership" class="forum-form">
            <h2>{{ __('forum_groups.actions.request') }}</h2>
            @foreach ($this->group['membership_questions'] as $questionIndex => $question)
                <label class="forum-form__field" wire:key="group-question-{{ $questionIndex }}">
                    <span>{{ $question }}</span>
                    <textarea wire:model="answers.{{ $questionIndex }}" rows="3" maxlength="1000"></textarea>
                    @error('answers.'.$questionIndex) <small role="alert">{{ $message }}</small> @enderror
                </label>
            @endforeach
            <button
                type="submit"
                class="forum-button forum-button--primary min-h-11"
                wire:loading.attr="disabled"
                wire:target="requestMembership"
            >
                <x-lucide-user-plus aria-hidden="true" />
                <span wire:loading.remove wire:target="requestMembership">
                    {{ $this->group['visibility_key'] === 'public' ? __('forum_groups.actions.join') : __('forum_groups.actions.request') }}
                </span>
                <span wire:loading wire:target="requestMembership">{{ __('forum_groups.actions.requesting') }}</span>
            </button>
        </form>
    @elseif ($this->group['membership_state'])
        <section aria-labelledby="your-group-membership-heading">
            <h2 id="your-group-membership-heading">{{ __('forum_groups.labels.your_membership') }}</h2>
            <p>
                {{ $this->group['membership_state'] }} · {{ $this->group['membership_role'] }} ·
                {{ __('forum_groups.labels.participating_as', [
                    'name' => $this->group['participating_as']['name'],
                    'type' => $this->group['participating_as']['type_label'],
                ]) }}
            </p>
            @if ($this->group['membership_rules_version'])
                <p>{{ __('forum_groups.labels.rules_version', ['version' => $this->group['membership_rules_version']]) }}</p>
            @endif
            @if ($this->group['membership_state_key'] === 'active' && $this->group['membership_role_key'] !== 'owner')
                <button
                    type="button"
                    class="forum-button min-h-11"
                    wire:click="leave"
                    wire:confirm="{{ __('forum_groups.actions.leave') }}"
                    wire:loading.attr="disabled"
                    wire:target="leave"
                >
                    <x-lucide-log-out aria-hidden="true" />
                    {{ __('forum_groups.actions.leave') }}
                </button>
            @endif
        </section>
    @endif

    <section class="border-y border-border-subtle py-4" aria-labelledby="member-content-heading">
        <h2 id="member-content-heading">{{ __('forum_groups.labels.member_content') }}</h2>
        <p>
            {{ $this->group['can_view_content']
                ? __('forum_groups.notices.member_content_available')
                : __('forum_groups.notices.member_content') }}
        </p>
    </section>

    @if ($this->group['can_view_content'])
        <livewire:forum.group-content-workspace
            :group-id="$this->group['id']"
            :key="'group-content-'.$this->group['id']"
        />
    @endif

    @if ($this->group['can_report'])
        <details class="forum-form">
            <summary class="forum-button min-h-11">
                <x-lucide-flag aria-hidden="true" />
                {{ __('forum_groups.actions.report') }}
            </summary>
            <form wire:submit="report" class="mt-4 grid gap-3">
                <label class="forum-form__field">
                    <span>{{ __('forum_groups.fields.report_reason') }}</span>
                    <select wire:model="reportReason">
                        <option value="">{{ __('forum_groups.fields.report_reason') }}</option>
                        @foreach ($this->reportReasonOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('reportReason') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_groups.fields.report_details') }}</span>
                    <textarea wire:model="reportDetails" rows="4" maxlength="1200"></textarea>
                </label>
                <label class="forum-form__check">
                    <input type="checkbox" wire:model="reportTruthfulnessConfirmed">
                    <span>{{ __('forum_groups.actions.truthfulness') }}</span>
                </label>
                <label class="forum-form__check">
                    <input type="checkbox" wire:model="reportImmediateSafety">
                    <span>{{ __('forum_groups.actions.immediate_safety') }}</span>
                </label>
                <label class="forum-form__check">
                    <input type="checkbox" wire:model="reportBlockOwner">
                    <span>{{ __('forum_groups.actions.report_and_block') }}</span>
                </label>
                <button class="forum-button forum-button--danger min-h-11" type="submit" wire:loading.attr="disabled" wire:target="report">
                    <x-lucide-flag aria-hidden="true" />
                    {{ __('forum_groups.actions.report') }}
                </button>
            </form>
        </details>
    @endif
</section>
