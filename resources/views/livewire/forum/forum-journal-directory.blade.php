<section class="grid gap-6" aria-labelledby="forum-journal-directory-heading">
    <header class="forum-header">
        <div class="forum-header__copy">
            <p class="forum-header__eyebrow">{{ __('forum_journals.page.eyebrow') }}</p>
            <h1 id="forum-journal-directory-heading">{{ __('forum_journals.page.heading') }}</h1>
            <p>{{ __('forum_journals.page.description') }}</p>
        </div>
    </header>

    <aside class="border-s-4 border-status-info py-3 ps-4" role="note">
        <p>{{ __('forum_journals.notices.community_journal') }}</p>
    </aside>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <p class="hidden border-s-4 border-status-warning py-3 ps-4" wire:offline.class.remove="hidden" role="status">
        {{ __('forum_journals.notices.offline') }}
    </p>

    <form class="forum-form grid gap-4 md:grid-cols-3">
        <label class="forum-form__field">
            <span>{{ __('forum_journals.filters.search') }}</span>
            <input
                type="search"
                wire:model.live.debounce.400ms="search"
                maxlength="120"
                placeholder="{{ __('forum_journals.filters.search_placeholder') }}"
            >
        </label>
        <label class="forum-form__field">
            <span>{{ __('forum_journals.filters.type') }}</span>
            <select wire:model.live="type">
                <option value="all">{{ __('forum_journals.filters.all_types') }}</option>
                @forelse ($this->typeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @empty
                    <option disabled>{{ __('forum_journals.empty.types') }}</option>
                @endforelse
            </select>
        </label>
        <label class="forum-form__field">
            <span>{{ __('forum_journals.filters.status') }}</span>
            <select wire:model.live="status">
                @forelse ($this->statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @empty
                    <option disabled>{{ __('forum_journals.empty.statuses') }}</option>
                @endforelse
            </select>
        </label>
    </form>

    <section aria-labelledby="forum-journal-results-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="forum-header__eyebrow">{{ __('forum_journals.page.your_journals') }}</p>
                <h2 id="forum-journal-results-heading">
                    {{ trans_choice('forum_journals.labels.journal_count', $this->journals->total(), ['count' => $this->journals->total()]) }}
                </h2>
            </div>
            <span wire:loading wire:target="search,type,status" role="status">
                {{ __('forum_journals.actions.filtering') }}
            </span>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($this->journals as $journal)
                <article class="forum-form" wire:key="forum-journal-{{ $journal['id'] }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <x-status-badge :label="$journal['type']" icon="notebook-tabs" />
                        <span class="text-sm">{{ $journal['status'] }}</span>
                    </div>
                    <div>
                        <h3 class="text-lg">{{ $journal['title'] }}</h3>
                        <p>{{ $journal['summary'] }}</p>
                    </div>
                    <dl class="grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="font-semibold">{{ __('forum_journals.fields.visibility') }}</dt>
                            <dd>{{ $journal['visibility'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_journals.fields.started_on') }}</dt>
                            <dd>{{ $journal['started_on'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_journals.labels.entries') }}</dt>
                            <dd>{{ $journal['entry_count'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_journals.labels.updated') }}</dt>
                            <dd>{{ $journal['updated_at'] }}</dd>
                        </div>
                    </dl>
                    <div class="flex flex-wrap gap-2">
                        @if ($journal['url'])
                            <a class="forum-button forum-button--primary min-h-11" href="{{ $journal['url'] }}" wire:navigate>
                                <x-lucide-arrow-up-right aria-hidden="true" />
                                {{ __('forum_journals.actions.open') }}
                            </a>
                        @endif
                        <a class="forum-button min-h-11" href="{{ $journal['export_url'] }}">
                            <x-lucide-download aria-hidden="true" />
                            {{ __('forum_journals.actions.export') }}
                        </a>
                    </div>
                </article>
            @empty
                <div class="forum-form md:col-span-2 xl:col-span-3">
                    <h3>{{ __('forum_journals.empty.journals_title') }}</h3>
                    <p>{{ __('forum_journals.empty.journals_description') }}</p>
                </div>
            @endforelse
        </div>
        <div class="mt-5">{{ $this->journals->links() }}</div>
    </section>

    <details class="forum-form">
        <summary class="forum-button min-h-11">
            <x-lucide-plus aria-hidden="true" />
            {{ __('forum_journals.page.create_heading') }}
        </summary>
        <form wire:submit="create" class="mt-4 grid gap-4">
            <p>{{ __('forum_journals.page.create_description') }}</p>

            @if ($errors->any())
                <x-forum-error-summary
                    :messages="$errors->getMessages()"
                    :heading="__('forum_journals.validation.summary')"
                />
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <label class="forum-form__field">
                    <span>{{ __('forum_journals.fields.title') }}</span>
                    <input type="text" wire:model="form.title" minlength="5" maxlength="180" required>
                    @error('form.title') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_journals.fields.type') }}</span>
                    <select wire:model="form.type" required>
                        @forelse ($this->typeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('forum_journals.empty.types') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_journals.fields.category') }}</span>
                    <select wire:model="form.categoryKey" required>
                        <option value="">{{ __('forum_journals.actions.select_category') }}</option>
                        @forelse ($this->categoryOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('forum_journals.empty.categories') }}</option>
                        @endforelse
                    </select>
                    @error('form.categoryKey') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_journals.fields.visibility') }}</span>
                    <select wire:model="form.visibility" required>
                        @forelse ($this->visibilityOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('forum_journals.empty.visibilities') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_journals.fields.started_on') }}</span>
                    <input type="date" wire:model="form.startedOn" min="1900-01-01" required>
                    @error('form.startedOn') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_journals.fields.language') }}</span>
                    <select wire:model="form.locale" required>
                        @forelse ($this->localeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('forum_journals.empty.locales') }}</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <label class="forum-form__field">
                <span>{{ __('forum_journals.fields.description') }}</span>
                <textarea wire:model="form.body" rows="6" minlength="10" maxlength="10000" required></textarea>
                @error('form.body') <small role="alert">{{ $message }}</small> @enderror
            </label>

            <button
                type="submit"
                class="forum-button forum-button--primary min-h-11 justify-self-start"
                wire:loading.attr="disabled"
                wire:target="create"
            >
                <x-lucide-notebook-pen aria-hidden="true" />
                <span wire:loading.remove wire:target="create">{{ __('forum_journals.actions.create') }}</span>
                <span wire:loading wire:target="create">{{ __('forum_journals.actions.creating') }}</span>
            </button>
        </form>
    </details>
</section>
