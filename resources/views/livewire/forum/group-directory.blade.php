<section class="grid gap-6" aria-labelledby="group-directory-heading">
    <header class="forum-header">
        <div class="forum-header__copy">
            <p class="forum-header__eyebrow">{{ __('forum_groups.page.eyebrow') }}</p>
            <h1 id="group-directory-heading">{{ __('forum_groups.page.heading') }}</h1>
            <p>{{ __('forum_groups.page.description') }}</p>
        </div>
    </header>

    <aside class="border-s-4 border-status-info py-3 ps-4">
        <p>{{ __('forum_groups.notices.privacy') }}</p>
    </aside>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <form class="forum-form grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(12rem,18rem)_auto]">
        <label class="forum-form__field">
            <span>{{ __('forum_groups.filters.search') }}</span>
            <input
                type="search"
                wire:model.live.debounce.400ms="search"
                maxlength="120"
                placeholder="{{ __('forum_groups.filters.search_placeholder') }}"
            >
        </label>
        <label class="forum-form__field">
            <span>{{ __('forum_groups.filters.visibility') }}</span>
            <select wire:model.live="visibility">
                @foreach ($this->visibilityOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <span class="self-end text-sm" role="status" aria-live="polite">
            {{ __('forum_groups.labels.groups', ['count' => $this->groups->total()]) }}
        </span>
    </form>

    @if ($this->invitations !== [])
        <section aria-labelledby="group-invitations-heading">
            <h2 id="group-invitations-heading">{{ __('forum_groups.page.invitations_heading') }}</h2>
            <p>{{ __('forum_groups.page.invitations_description') }}</p>
            <div class="mt-3 divide-y divide-border-subtle border-y border-border-subtle">
                @foreach ($this->invitations as $invitation)
                    <article class="grid gap-2 py-4 sm:grid-cols-[minmax(0,1fr)_auto]" wire:key="group-invitation-{{ $invitation['id'] }}">
                        <div>
                            <h3>{{ $invitation['group_name'] }}</h3>
                            <p>{{ __('forum_groups.labels.invited_by', ['name' => $invitation['inviter_name']]) }}</p>
                            <p>{{ $invitation['role'] }} · {{ __('forum_groups.labels.expires', ['date' => $invitation['expires_at']]) }}</p>
                            @if ($invitation['message'])
                                <p>{{ $invitation['message'] }}</p>
                            @endif
                        </div>
                        <a class="forum-button min-h-11 self-center" href="{{ $invitation['group_url'] }}" wire:navigate>
                            <x-lucide-arrow-up-right aria-hidden="true" />
                            {{ __('forum_groups.actions.open') }}
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section aria-labelledby="group-results-heading">
        <h2 id="group-results-heading">{{ __('forum_groups.filters.all') }}</h2>
        <div class="mt-3 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($this->groups as $group)
                <article class="forum-form" wire:key="persistent-group-{{ $group['id'] }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <x-status-badge :label="$group['visibility']" icon="users-round" />
                        <span class="text-sm">{{ $group['status'] }}</span>
                    </div>
                    <div>
                        <h3 class="text-lg">{{ $group['name'] }}</h3>
                        <p>{{ $group['description'] }}</p>
                    </div>
                    <dl class="grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="font-semibold">{{ __('forum_groups.roles.owner') }}</dt>
                            <dd>{{ $group['owner_name'] ?? __('forum_groups.system.platform_managed') }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_groups.membership_states.active') }}</dt>
                            <dd>{{ __('forum_groups.labels.members', ['count' => $group['member_count']]) }}</dd>
                        </div>
                        @if ($group['location_scope'])
                            <div>
                                <dt class="font-semibold">{{ __('forum_groups.fields.location_scope') }}</dt>
                                <dd>{{ $group['location_scope'] }}</dd>
                            </div>
                        @endif
                        @if ($group['taxa'] !== '')
                            <div>
                                <dt class="font-semibold">{{ __('forum_groups.labels.species') }}</dt>
                                <dd><i>{{ $group['taxa'] }}</i></dd>
                            </div>
                        @endif
                    </dl>
                    @if ($group['membership_state'])
                        <p>{{ __('forum_groups.labels.your_membership') }}: {{ $group['membership_state'] }}</p>
                    @endif
                    <a class="forum-button forum-button--primary min-h-11" href="{{ $group['url'] }}" wire:navigate>
                        <x-lucide-arrow-up-right aria-hidden="true" />
                        {{ __('forum_groups.actions.open') }}
                    </a>
                </article>
            @empty
                <div class="forum-form md:col-span-2 xl:col-span-3">
                    <h3>{{ __('forum_groups.empty.groups_title') }}</h3>
                    <p>{{ __('forum_groups.empty.groups_description') }}</p>
                </div>
            @endforelse
        </div>
        <div class="mt-5">{{ $this->groups->links() }}</div>
    </section>

    <details class="forum-form">
        <summary class="forum-button min-h-11">
            <x-lucide-plus aria-hidden="true" />
            {{ __('forum_groups.page.create_heading') }}
        </summary>
        <form wire:submit="create" class="mt-4 grid gap-4">
            <p>{{ __('forum_groups.page.create_description') }}</p>

            @if ($errors->any())
                <x-forum-error-summary
                    :messages="$errors->getMessages()"
                    :heading="__('forum_groups.validation.summary')"
                />
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <label class="forum-form__field">
                    <span>{{ __('forum_groups.fields.name') }}</span>
                    <input type="text" wire:model="form.name" minlength="3" maxlength="160">
                    @error('form.name') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_groups.fields.visibility') }}</span>
                    <select wire:model="form.visibility">
                        @foreach ($this->creationVisibilityOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_groups.fields.language') }}</span>
                    <select wire:model="form.defaultLocale">
                        @foreach ($this->localeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_groups.fields.location_scope') }}</span>
                    <input
                        type="text"
                        wire:model="form.locationScope"
                        maxlength="160"
                        placeholder="{{ __('forum_groups.placeholders.location_scope') }}"
                    >
                    @error('form.locationScope') <small role="alert">{{ $message }}</small> @enderror
                </label>
            </div>

            <label class="forum-form__field">
                <span>{{ __('forum_groups.fields.description') }}</span>
                <textarea wire:model="form.description" rows="5" minlength="20" maxlength="3000"></textarea>
                @error('form.description') <small role="alert">{{ $message }}</small> @enderror
            </label>
            <label class="forum-form__field">
                <span>{{ __('forum_groups.fields.rules') }}</span>
                <textarea wire:model="form.rulesText" rows="5" maxlength="5000"></textarea>
                @error('form.rulesText') <small role="alert">{{ $message }}</small> @enderror
            </label>
            <label class="forum-form__field">
                <span>{{ __('forum_groups.fields.membership_questions') }}</span>
                <textarea wire:model="form.questionsText" rows="4" maxlength="3000"></textarea>
                @error('form.questionsText') <small role="alert">{{ $message }}</small> @enderror
            </label>

            <livewire:forum.animal-taxonomy-selector
                wire:model.live="form.taxonIds"
                input-name="group_taxon_ids"
                :selection-limit="10"
            />

            <button
                type="submit"
                class="forum-button forum-button--primary min-h-11 justify-self-start"
                wire:loading.attr="disabled"
                wire:target="create"
            >
                <x-lucide-plus aria-hidden="true" />
                <span wire:loading.remove wire:target="create">{{ __('forum_groups.actions.create') }}</span>
                <span wire:loading wire:target="create">{{ __('forum_groups.actions.creating') }}</span>
            </button>
        </form>
    </details>
</section>
