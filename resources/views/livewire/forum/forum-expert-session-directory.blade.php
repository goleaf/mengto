<section class="grid gap-6" aria-labelledby="expert-session-directory-heading">
    <header class="forum-header">
        <div class="forum-header__copy">
            <p class="forum-header__eyebrow">{{ __('forum_expert_sessions.page.eyebrow') }}</p>
            <h1 id="expert-session-directory-heading">{{ __('forum_expert_sessions.page.heading') }}</h1>
            <p>{{ __('forum_expert_sessions.page.description') }}</p>
        </div>
    </header>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <p class="hidden border-s-4 border-status-warning py-3 ps-4" wire:offline.class.remove="hidden" role="status">
        {{ __('forum_expert_sessions.notices.offline') }}
    </p>

    <form class="forum-form grid gap-4 md:grid-cols-3" aria-label="{{ __('forum_expert_sessions.filters.label') }}">
        <label class="forum-form__field">
            <span>{{ __('forum_expert_sessions.filters.search') }}</span>
            <input
                type="search"
                wire:model.live.debounce.400ms="search"
                maxlength="120"
                placeholder="{{ __('forum_expert_sessions.filters.search_placeholder') }}"
            >
        </label>
        <label class="forum-form__field">
            <span>{{ __('forum_expert_sessions.filters.scope') }}</span>
            <select wire:model.live="scope">
                <option value="all">{{ __('forum_expert_sessions.filters.all_scopes') }}</option>
                @forelse ($this->scopeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @empty
                @endforelse
            </select>
        </label>
        <label class="forum-form__field">
            <span>{{ __('forum_expert_sessions.filters.period') }}</span>
            <select wire:model.live="period">
                <option value="upcoming">{{ __('forum_expert_sessions.filters.upcoming') }}</option>
                <option value="past">{{ __('forum_expert_sessions.filters.past') }}</option>
                <option value="all">{{ __('forum_expert_sessions.filters.all_periods') }}</option>
            </select>
        </label>
        <span class="md:col-span-3" wire:loading wire:target="search,scope,period" role="status">
            {{ __('forum_expert_sessions.actions.filtering') }}
        </span>
    </form>

    <section aria-labelledby="expert-session-results-heading">
        <h2 id="expert-session-results-heading">
            {{ trans_choice('forum_expert_sessions.labels.session_count', $this->sessions->total(), ['count' => $this->sessions->total()]) }}
        </h2>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($this->sessions as $session)
                <article class="forum-form" wire:key="expert-session-{{ $session['id'] }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <x-status-badge :label="$session['phase']" icon="messages-square" />
                        <span class="text-sm">{{ $session['jurisdiction'] }}</span>
                    </div>
                    <div>
                        <h3 class="text-lg">{{ $session['title'] }}</h3>
                        <p>{{ $session['summary'] }}</p>
                    </div>
                    <dl class="grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="font-semibold">{{ __('forum_expert_sessions.fields.host') }}</dt>
                            <dd><a class="inline-flex min-h-11 items-center" href="{{ $session['host_profile_url'] }}">{{ $session['host_name'] }}</a></dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_expert_sessions.fields.professional_scope') }}</dt>
                            <dd>{{ $session['professional_scope'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_expert_sessions.fields.starts_at') }}</dt>
                            <dd>{{ $session['starts_at'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_expert_sessions.labels.activity') }}</dt>
                            <dd>{{ __('forum_expert_sessions.labels.activity_counts', ['questions' => $session['questions'], 'answers' => $session['answers']]) }}</dd>
                        </div>
                    </dl>
                    <a class="forum-button forum-button--primary min-h-11 justify-self-start" href="{{ $session['url'] }}" wire:navigate>
                        <x-lucide-arrow-up-right aria-hidden="true" />
                        {{ __('forum_expert_sessions.actions.open') }}
                    </a>
                </article>
            @empty
                <div class="forum-form md:col-span-2 xl:col-span-3">
                    <h3>{{ __('forum_expert_sessions.empty.sessions_title') }}</h3>
                    <p>{{ __('forum_expert_sessions.empty.sessions_description') }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-5">{{ $this->sessions->links() }}</div>
    </section>

    @if ($this->canCreate)
        <details class="forum-form">
            <summary class="forum-button min-h-11">
                <x-lucide-calendar-plus aria-hidden="true" />
                {{ __('forum_expert_sessions.page.create_heading') }}
            </summary>
            <form wire:submit="create" class="mt-4 grid gap-5" wire:dirty.class="border-status-warning">
                <p>{{ __('forum_expert_sessions.page.create_description') }}</p>

                @if ($errors->any())
                    <p class="form-errors" role="alert">{{ __('forum_expert_sessions.validation.summary') }}</p>
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="forum-form__field">
                        <span>{{ __('forum_expert_sessions.fields.expert_profile') }}</span>
                        <select wire:model.live="form.expertProfileId" required>
                            @forelse ($this->profileOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                            @endforelse
                        </select>
                        @error('form.expertProfileId') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_expert_sessions.fields.professional_scope') }}</span>
                        <input type="text" wire:model="form.professionalScope" maxlength="120" required>
                        @error('form.professionalScope') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_expert_sessions.fields.jurisdiction') }}</span>
                        <input type="text" wire:model="form.jurisdiction" maxlength="120" required>
                        @error('form.jurisdiction') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_expert_sessions.fields.locale') }}</span>
                        <select wire:model="form.locale" required>
                            @forelse ($this->localeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                            @endforelse
                        </select>
                    </label>
                    <label class="forum-form__field md:col-span-2">
                        <span>{{ __('forum_expert_sessions.fields.title') }}</span>
                        <input type="text" wire:model="form.title" minlength="8" maxlength="180" required>
                        @error('form.title') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field md:col-span-2">
                        <span>{{ __('forum_expert_sessions.fields.summary') }}</span>
                        <textarea wire:model="form.summary" rows="5" minlength="20" maxlength="10000" required></textarea>
                        @error('form.summary') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_expert_sessions.fields.question_opens_at') }}</span>
                        <input type="datetime-local" wire:model="form.questionOpensAt" required>
                        @error('form.questionOpensAt') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_expert_sessions.fields.question_closes_at') }}</span>
                        <input type="datetime-local" wire:model="form.questionClosesAt" required>
                        @error('form.questionClosesAt') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_expert_sessions.fields.starts_at') }}</span>
                        <input type="datetime-local" wire:model="form.startsAt" required>
                        @error('form.startsAt') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_expert_sessions.fields.ends_at') }}</span>
                        <input type="datetime-local" wire:model="form.endsAt" required>
                        @error('form.endsAt') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field md:col-span-2">
                        <span>{{ __('forum_expert_sessions.fields.timezone') }}</span>
                        <input type="text" wire:model="form.timezone" maxlength="64" required>
                    </label>
                </div>

                <button class="forum-button forum-button--primary min-h-11 justify-self-start" type="submit" wire:loading.attr="disabled" wire:target="create">
                    <x-lucide-calendar-plus aria-hidden="true" />
                    <span wire:loading.remove wire:target="create">{{ __('forum_expert_sessions.actions.create') }}</span>
                    <span wire:loading wire:target="create">{{ __('forum_expert_sessions.actions.creating') }}</span>
                </button>
            </form>
        </details>
    @endif
</section>
