<section class="grid gap-5" aria-labelledby="mentorship-page-heading">
    <x-page-header
        :eyebrow="__('forum_mentorship.page.eyebrow')"
        :title="__('forum_mentorship.page.heading')"
        :description="__('forum_mentorship.page.description')"
        heading-id="mentorship-page-heading"
        data-section="forum-mentorship-header"
    />

    <aside class="border-s-4 border-status-warning py-3 ps-4" aria-labelledby="mentorship-safety-heading">
        <h2 id="mentorship-safety-heading" class="text-base font-semibold">
            {{ __('forum_mentorship.safety.heading') }}
        </h2>
        <p>{{ __('forum_mentorship.safety.boundaries') }}</p>
        <p class="mt-2">{{ __('forum_mentorship.safety.urgent') }}</p>
    </aside>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <form wire:submit="refreshMatches" class="forum-form" aria-labelledby="mentor-filter-heading">
        <div>
            <h2 id="mentor-filter-heading">{{ __('forum_mentorship.discovery.filters') }}</h2>
            <p>{{ __('forum_mentorship.discovery.description') }}</p>
        </div>

        @if ($errors->any())
            <x-forum-error-summary
                :messages="$errors->getMessages()"
                :heading="__('forum_mentorship.validation.summary')"
            />
        @endif

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <label class="forum-form__field">
                <span>{{ __('forum_mentorship.fields.mentorship_type') }}</span>
                <select wire:model="type">
                    @foreach ($this->typeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="forum-form__field">
                <span>{{ __('forum_mentorship.fields.language') }}</span>
                <select wire:model="language">
                    @foreach ($this->localeOptions as $locale => $label)
                        <option value="{{ $locale }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="forum-form__field">
                <span>{{ __('forum_mentorship.fields.category') }}</span>
                <select wire:model="forumCategoryId">
                    <option value="">{{ __('forum_mentorship.discovery.all_categories') }}</option>
                    @foreach ($this->categoryOptions as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="forum-form__field">
                <span>{{ __('forum_mentorship.fields.location_scope') }}</span>
                <input
                    type="text"
                    wire:model="locationScope"
                    maxlength="160"
                    placeholder="{{ __('forum_mentorship.discovery.location_placeholder') }}"
                >
            </label>
        </div>

        <livewire:forum.animal-taxonomy-selector
            wire:model.live="taxonIds"
            input-name="taxon_id"
            :selection-limit="1"
        />

        <button
            type="submit"
            class="forum-button forum-button--primary min-h-11"
            wire:loading.attr="disabled"
            wire:target="refreshMatches"
        >
            <x-ui-icon name="search" />
            <span wire:loading.remove wire:target="refreshMatches">
                {{ __('forum_mentorship.discovery.refresh') }}
            </span>
            <span wire:loading wire:target="refreshMatches">
                {{ __('forum_mentorship.discovery.updating') }}
            </span>
        </button>
    </form>

    <section aria-labelledby="mentor-results-heading">
        <h2 id="mentor-results-heading">{{ __('forum_mentorship.discovery.results') }}</h2>

        <div class="mt-3 grid gap-4 lg:grid-cols-2">
            @forelse ($this->matches as $match)
                <article class="forum-form" wire:key="mentor-match-{{ $match['scope_id'] }}">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg">{{ $match['mentor_name'] }}</h3>
                            <p class="font-semibold">{{ $match['headline'] }}</p>
                        </div>
                        <x-status-badge :label="$match['type']" icon="users-round" />
                    </div>

                    <p>{{ $match['summary'] }}</p>

                    <dl class="grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="font-semibold">{{ __('forum_mentorship.fields.languages') }}</dt>
                            <dd>
                                {{ $match['languages_label'] }}
                            </dd>
                        </div>
                        @if ($match['location_scope'])
                            <div>
                                <dt class="font-semibold">{{ __('forum_mentorship.fields.location_scope') }}</dt>
                                <dd>{{ $match['location_scope'] }}</dd>
                            </div>
                        @endif
                        @if ($match['category'])
                            <div>
                                <dt class="font-semibold">{{ __('forum_mentorship.fields.category') }}</dt>
                                <dd>{{ $match['category'] }}</dd>
                            </div>
                        @endif
                        @if ($match['taxon'])
                            <div>
                                <dt class="font-semibold">{{ __('forum_mentorship.fields.taxon') }}</dt>
                                <dd><i>{{ $match['taxon'] }}</i></dd>
                            </div>
                        @endif
                    </dl>

                    <p class="flex items-start gap-2 text-sm">
                        @if ($match['professionally_verified'])
                            <x-ui-icon name="badge-check" class="text-status-success" />
                            {{ __('forum_mentorship.discovery.professional_verified') }}
                        @else
                            <x-ui-icon name="users-round" />
                            {{ __('forum_mentorship.discovery.peer_only') }}
                        @endif
                    </p>

                    <div>
                        <strong>{{ __('forum_mentorship.discovery.match_reasons') }}</strong>
                        <ul class="mt-2 list-disc ps-5 text-sm">
                            @foreach ($match['reasons'] as $reason)
                                <li>{{ $reason }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <details>
                        <summary class="forum-button min-h-11">
                            <x-ui-icon name="send" />
                            {{ __('forum_mentorship.discovery.request_heading') }}
                        </summary>
                        <form wire:submit="request({{ $match['scope_id'] }})" class="mt-3 grid gap-3">
                            <label class="forum-form__field">
                                <span>{{ __('forum_mentorship.fields.request_message') }}</span>
                                <textarea wire:model="form.message" rows="5" minlength="20" maxlength="3000"></textarea>
                                @error('form.message') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__check">
                                <input type="checkbox" wire:model="form.safetyAcknowledged">
                                <span>{{ __('forum_mentorship.fields.safety_acknowledgement') }}</span>
                            </label>
                            @error('form.safetyAcknowledged') <small role="alert">{{ $message }}</small> @enderror
                            <button
                                type="submit"
                                class="forum-button forum-button--primary min-h-11"
                                wire:loading.attr="disabled"
                                wire:target="request"
                            >
                                <x-ui-icon name="send" />
                                <span wire:loading.remove wire:target="request">
                                    {{ __('forum_mentorship.discovery.send_request') }}
                                </span>
                                <span wire:loading wire:target="request">
                                    {{ __('forum_mentorship.discovery.sending_request') }}
                                </span>
                            </button>
                        </form>
                    </details>
                </article>
            @empty
                <div class="forum-form lg:col-span-2">
                    <h3>{{ __('forum_mentorship.discovery.empty_title') }}</h3>
                    <p>{{ __('forum_mentorship.discovery.empty_description') }}</p>
                </div>
            @endforelse
        </div>
    </section>
</section>
