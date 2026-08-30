<section class="grid gap-6" aria-labelledby="forum-event-directory-heading" data-section="event-directory">
    <x-page-header
        :eyebrow="__('forum_events.page.eyebrow')"
        :title="$createOnly ? __('forum_events.page.create_heading') : __('forum_events.page.heading')"
        :description="$createOnly ? __('forum_events.page.create_description') : __('forum_events.page.description')"
        heading-id="forum-event-directory-heading"
        data-section="forum-event-directory-header"
    />

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <p class="hidden border-s-4 border-status-warning py-3 ps-4" wire:offline.class.remove="hidden" role="status">
        {{ __('forum_events.notices.offline') }}
    </p>

    @unless ($createOnly)
    <nav class="flex gap-2 overflow-x-auto pb-1" aria-label="{{ __('forum_events.tabs.label') }}">
        @foreach (['discover', 'my', 'invitations'] as $meetupScope)
            <button
                type="button"
                class="forum-button min-h-11 shrink-0"
                wire:click="$set('scope', '{{ $meetupScope }}')"
                @if ($scope === $meetupScope) aria-current="page" @endif
            >
                {{ __('forum_events.tabs.'.$meetupScope) }}
            </button>
        @endforeach
    </nav>
    <form class="forum-form grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <label class="forum-form__field sm:col-span-2 xl:col-span-1">
            <span>{{ __('forum_events.filters.search') }}</span>
            <input
                type="search"
                wire:model.live.debounce.400ms="search"
                maxlength="120"
                placeholder="{{ __('forum_events.filters.search_placeholder') }}"
            >
        </label>
        <label class="forum-form__field">
            <span>{{ __('forum_events.filters.type') }}</span>
            <select wire:model.live="type">
                <option value="all">{{ __('forum_events.filters.all_types') }}</option>
                @forelse ($this->typeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @empty
                @endforelse
            </select>
        </label>
        <label class="forum-form__field">
            <span>{{ __('forum_events.filters.format') }}</span>
            <select wire:model.live="format">
                <option value="all">{{ __('forum_events.filters.all_formats') }}</option>
                @forelse ($this->formatOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @empty
                @endforelse
            </select>
        </label>
        <label class="forum-form__field">
            <span>{{ __('forum_events.filters.status') }}</span>
            <select wire:model.live="period">
                <option value="upcoming">{{ __('forum_events.filters.upcoming') }}</option>
                <option value="past">{{ __('forum_events.filters.past') }}</option>
                <option value="all">{{ __('forum_events.filters.all_periods') }}</option>
            </select>
        </label>
    </form>

    <section aria-labelledby="forum-event-results-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 id="forum-event-results-heading">
                {{ trans_choice('forum_events.labels.event_count', $this->events->total(), ['count' => $this->events->total()]) }}
            </h2>
            <span wire:loading wire:target="search,type,format,period" role="status">
                {{ __('forum_journals.actions.filtering') }}
            </span>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($this->events as $event)
                <article class="forum-form" wire:key="forum-event-{{ $event['id'] }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <x-status-badge :label="$event['type']" icon="calendar-days" />
                        <div class="flex flex-wrap gap-2">
                            @if ($event['participation_status'])
                                <x-status-badge :label="$event['participation_status']" icon="circle-dot" />
                            @endif
                            <span class="text-sm">{{ $event['status'] }}</span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg">{{ $event['title'] }}</h3>
                        <p>{{ $event['summary'] }}</p>
                    </div>

                    <dl class="grid gap-2 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="font-semibold">{{ __('forum_events.fields.starts_at') }}</dt>
                            <dd>{{ $event['starts_at'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_events.fields.format') }}</dt>
                            <dd>{{ $event['format'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_events.fields.pet_participation_mode') }}</dt>
                            <dd>{{ $event['pet_participation'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_events.fields.location_scope') }}</dt>
                            <dd>{{ $event['location'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_events.fields.cost_minor') }}</dt>
                            <dd>{{ $event['cost'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_events.detail.organizer') }}</dt>
                            <dd class="inline-flex flex-wrap items-center gap-1">
                                <span>{{ $event['organizer_name'] }}</span>
                                @if ($event['organizer_verified'])
                                    <x-ui-icon name="badge-check" size="sm" label="{{ __('forum_events.detail.verified_organizer') }}" />
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('forum_events.fields.capacity') }}</dt>
                            <dd>
                                @if ($event['capacity'] === null)
                                    {{ __('forum_events.labels.unlimited_capacity', ['confirmed' => $event['confirmed_count']]) }}
                                @else
                                    {{ __('forum_events.labels.capacity', ['confirmed' => $event['confirmed_count'], 'capacity' => $event['capacity']]) }}
                                @endif
                            </dd>
                        </div>
                    </dl>

                    @if ($event['taxa'] !== [])
                        <ul class="flex flex-wrap gap-2" aria-label="{{ __('forum_events.fields.taxon_ids') }}">
                            @forelse ($event['taxa'] as $taxon)
                                <li class="forum-topic-card__tag">{{ $taxon }}</li>
                            @empty
                            @endforelse
                        </ul>
                    @endif

                    @if ($event['accessibility'])
                        <p class="inline-flex items-start gap-2 text-sm">
                            <x-ui-icon name="accessibility" size="sm" class="mt-0.5 shrink-0" />
                            <span><strong>{{ $event['accessibility_status'] }}:</strong> {{ $event['accessibility'] }}</span>
                        </p>
                    @else
                        <p class="inline-flex items-start gap-2 text-sm">
                            <x-ui-icon name="accessibility" size="sm" class="mt-0.5 shrink-0" />
                            <span>{{ $event['accessibility_status'] }}</span>
                        </p>
                    @endif

                    <a class="forum-button forum-button--primary min-h-11 justify-self-start" href="{{ $event['url'] }}" wire:navigate>
                        <x-ui-icon name="arrow-up-right" />
                        {{ $event['action_label'] }}
                    </a>
                </article>
            @empty
                <div class="forum-form md:col-span-2 xl:col-span-3">
                    <h3>{{ __('forum_events.empty.events_title') }}</h3>
                    <p>{{ __('forum_events.empty.events_description') }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-5">{{ $this->events->links() }}</div>
    </section>

    @if ($this->canCreate)
        <a class="forum-button forum-button--primary min-h-11 justify-self-start" href="{{ route('meetups.create') }}" wire:navigate>
            <x-ui-icon name="calendar-plus" />
            {{ __('forum_events.page.create_heading') }}
        </a>
    @endif
    @endunless

    @if ($createOnly && $this->canCreate)
        <div class="forum-form">
            <form wire:submit="create" class="mt-4 grid gap-5" wire:dirty.class="border-status-warning">
                <p>{{ __('forum_events.page.create_description') }}</p>

                @if ($errors->any())
                    <x-forum-error-summary
                        :messages="$errors->getMessages()"
                        :heading="__('forum_events.validation.summary')"
                    />
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="forum-form__field md:col-span-2">
                        <span>{{ __('forum_events.fields.title') }}</span>
                        <input type="text" wire:model="form.title" minlength="4" maxlength="180" required>
                        @error('form.title') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field md:col-span-2">
                        <span>{{ __('forum_events.fields.summary') }}</span>
                        <textarea wire:model="form.summary" rows="5" minlength="10" maxlength="10000" required></textarea>
                        @error('form.summary') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.type') }}</span>
                        <select wire:model="form.type" required>
                            @forelse ($this->typeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                            @endforelse
                        </select>
                    </label>
                    <fieldset class="forum-form__field md:col-span-2">
                        <legend>{{ __('forum_events.fields.visibility') }}</legend>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @forelse ($this->visibilityOptions as $value => $label)
                                <label class="inline-flex min-h-11 items-center gap-3">
                                    <input type="radio" wire:model.live="form.visibility" value="{{ $value }}" required>
                                    <span>{{ $label }}</span>
                                </label>
                            @empty
                            @endforelse
                        </div>
                    </fieldset>
                    @if ($form->visibility === 'organization')
                        <label class="forum-form__field md:col-span-2">
                            <span>{{ __('forum_events.fields.responsible_organization') }}</span>
                            <select wire:model="form.responsibleOrganizationId" required>
                                <option value="">{{ __('forum_events.fields.responsible_organization') }}</option>
                                @forelse ($this->organizationOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @empty
                                @endforelse
                            </select>
                            @error('form.responsibleOrganizationId') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    @endif
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.format') }}</span>
                        <select wire:model.live="form.format" required>
                            @forelse ($this->formatOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                            @endforelse
                        </select>
                    </label>
                    <fieldset class="forum-form__field md:col-span-2">
                        <legend>{{ __('forum_events.fields.registration_policy') }}</legend>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @forelse ($this->registrationPolicyOptions as $value => $label)
                                <label class="inline-flex min-h-11 items-center gap-3">
                                    <input type="radio" wire:model="form.registrationPolicy" value="{{ $value }}" required>
                                    <span>{{ $label }}</span>
                                </label>
                            @empty
                            @endforelse
                        </div>
                    </fieldset>
                    <fieldset class="forum-form__field md:col-span-2">
                        <legend>{{ __('forum_events.fields.pet_participation_mode') }}</legend>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @forelse ($this->petParticipationOptions as $value => $label)
                                <label class="inline-flex min-h-11 items-center gap-3">
                                    <input type="radio" wire:model="form.petParticipationMode" value="{{ $value }}" required>
                                    <span>{{ $label }}</span>
                                </label>
                            @empty
                            @endforelse
                        </div>
                        @error('form.petParticipationMode') <small role="alert">{{ $message }}</small> @enderror
                    </fieldset>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.starts_at') }}</span>
                        <input type="datetime-local" wire:model="form.startsAt" required>
                        @error('form.startsAt') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.ends_at') }}</span>
                        <input type="datetime-local" wire:model="form.endsAt" required>
                        @error('form.endsAt') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.timezone') }}</span>
                        <input type="text" wire:model="form.timezone" maxlength="64" required>
                        @error('form.timezone') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.capacity') }}</span>
                        <input type="number" wire:model="form.capacity" min="1" max="100000">
                        @error('form.capacity') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    @if ($form->format !== 'online')
                        <label class="forum-form__field">
                            <span>{{ __('places.fields.place') }}</span>
                            <select wire:model.live="form.placeId">
                                <option value="">{{ __('places.builder.manual_public_location') }}</option>
                                @forelse ($this->placeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @empty
                                @endforelse
                            </select>
                            @error('form.placeId') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        @if ($form->placeId)
                            @if ($this->venueOptions !== [])
                                <label class="forum-form__field">
                                    <span>{{ __('places.fields.venue') }}</span>
                                    <select wire:model="form.venueId">
                                        <option value="">{{ __('places.builder.no_venue_area') }}</option>
                                        @forelse ($this->venueOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    @error('form.venueId') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                            @endif
                        @else
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.location_scope') }}</span>
                                <input type="text" wire:model="form.locationScope" maxlength="190" required>
                                @error('form.locationScope') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field md:col-span-2">
                                <span>{{ __('forum_events.fields.exact_location') }}</span>
                                <textarea wire:model="form.exactLocation" rows="3" maxlength="2000"></textarea>
                                <small>{{ __('forum_events.notices.exact_location_private') }}</small>
                                @error('form.exactLocation') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                        @endif
                    @endif
                    @if ($form->format !== 'physical')
                        <label class="forum-form__field md:col-span-2">
                            <span>{{ __('forum_events.fields.online_url') }}</span>
                            <input type="url" wire:model="form.onlineUrl" maxlength="2000" required>
                            @error('form.onlineUrl') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    @endif
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.cost_minor') }}</span>
                        <input type="number" wire:model="form.costMinor" min="0" max="100000000" required>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.currency') }}</span>
                        <input type="text" wire:model="form.currency" minlength="3" maxlength="3" required>
                    </label>
                    @if ($form->costMinor > 0)
                        <label class="forum-form__field md:col-span-2">
                            <span>{{ __('forum_events.fields.refund_policy') }}</span>
                            <textarea wire:model="form.refundPolicy" rows="3" maxlength="5000" required></textarea>
                        </label>
                    @endif
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.photo_consent_mode') }}</span>
                        <select wire:model="form.photoConsentMode" required>
                            @forelse ($this->photoConsentOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                            @endforelse
                        </select>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.locale') }}</span>
                        <select wire:model="form.locale" required>
                            @forelse ($this->localeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                            @endforelse
                        </select>
                    </label>
                </div>

                <label class="inline-flex min-h-11 items-center gap-3">
                    <input type="checkbox" wire:model="form.waitlistEnabled">
                    <span>{{ __('forum_events.fields.waitlist_enabled') }}</span>
                </label>

                <label class="forum-form__field">
                    <span>{{ __('forum_events.fields.attendance_requirements') }}</span>
                    <textarea wire:model="form.attendanceRequirements" rows="3" maxlength="5000"></textarea>
                </label>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.vaccination_requirements') }}</span>
                        <textarea wire:model="form.vaccinationRequirements" rows="3" maxlength="5000"></textarea>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.vaccination_jurisdiction') }}</span>
                        <input type="text" wire:model="form.vaccinationJurisdiction" maxlength="120">
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.minimum_animal_age_months') }}</span>
                        <input type="number" wire:model="form.minimumAnimalAgeMonths" min="0" max="1200">
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.maximum_animal_age_months') }}</span>
                        <input type="number" wire:model="form.maximumAnimalAgeMonths" min="0" max="1200">
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.accessibility_status') }}</span>
                        <select wire:model="form.accessibilityStatus" required>
                            @forelse ($this->accessibilityStatusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                            @endforelse
                        </select>
                        @error('form.accessibilityStatus') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.accessibility_information') }}</span>
                        <textarea wire:model="form.accessibilityInformation" rows="3" maxlength="5000"></textarea>
                    </label>
                </div>
                <label class="forum-form__field">
                    <span>{{ __('forum_events.fields.animal_welfare_rules') }}</span>
                    <textarea wire:model="form.animalWelfareRules" rows="4" minlength="10" maxlength="10000" required></textarea>
                    @error('form.animalWelfareRules') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_events.fields.emergency_contact_plan') }}</span>
                    <textarea wire:model="form.emergencyContactPlan" rows="4" minlength="10" maxlength="10000" required></textarea>
                    @error('form.emergencyContactPlan') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <div>
                    <span class="mb-2 block text-sm font-semibold">{{ __('forum_events.fields.taxon_ids') }}</span>
                    <livewire:forum.animal-taxonomy-selector
                        wire:model="form.taxonIds"
                        :selected="$form->taxonIds"
                        input-name="taxon_ids[]"
                        :selection-limit="5"
                    />
                    @error('form.taxonIds') <small role="alert">{{ $message }}</small> @enderror
                    @error('form.taxonIds.*') <small role="alert">{{ $message }}</small> @enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="forum-button min-h-11"
                        wire:click="saveDraft"
                        wire:loading.attr="disabled"
                        wire:target="saveDraft,create"
                    >
                        <x-ui-icon name="save" />
                        {{ __('forum_events.actions.save_draft') }}
                    </button>
                    <button
                        type="submit"
                        class="forum-button forum-button--primary min-h-11"
                        wire:loading.attr="disabled"
                        wire:target="saveDraft,create"
                    >
                        <x-ui-icon name="calendar-plus" />
                        <span wire:loading.remove wire:target="create">{{ __('forum_events.actions.create') }}</span>
                        <span wire:loading wire:target="create">{{ __('forum_events.actions.creating') }}</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</section>
