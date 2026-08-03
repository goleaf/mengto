<section class="grid gap-6" aria-labelledby="forum-event-heading" data-section="event-workspace">
    <a class="forum-button min-h-11 justify-self-start" href="{{ route('meetups.index') }}" wire:navigate>
        <x-lucide-arrow-left aria-hidden="true" />
        {{ __('forum_events.detail.back') }}
    </a>

    <header class="forum-header overflow-hidden">
        @if ($this->event['image'])
            <img
                src="{{ $this->event['image'] }}"
                alt="{{ $this->event['image_alt'] }}"
                class="aspect-[16/7] w-full object-cover"
                width="1440"
                height="630"
            >
        @endif
        <div class="forum-header__copy">
            <div class="flex flex-wrap gap-2">
                <x-status-badge :label="$this->event['type']" icon="calendar-days" />
                <x-status-badge :label="$this->event['status']" icon="circle-dot" />
            </div>
            <h1 id="forum-event-heading">{{ $this->event['title'] }}</h1>
            <p>{{ $this->event['summary'] }}</p>
            <p class="inline-flex flex-wrap items-center gap-2">
                <x-lucide-user-round class="size-4 shrink-0" aria-hidden="true" />
                {{ __('forum_events.labels.organizer_by', ['name' => $this->event['organizer_name']]) }}
                @if ($this->event['organizer_verified'])
                    <span class="inline-flex items-center gap-1 font-semibold">
                        <x-lucide-badge-check class="size-4" aria-hidden="true" />
                        {{ __('forum_events.detail.verified_organizer') }}
                    </span>
                @endif
            </p>
        </div>
    </header>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <p class="hidden border-s-4 border-status-warning py-3 ps-4" wire:offline.class.remove="hidden" role="status">
        {{ __('forum_events.notices.offline') }}
    </p>

    @if ($this->currentInvitation)
        <section class="border-s-4 border-status-info py-3 ps-4" aria-labelledby="event-invitation-heading">
            <h2 id="event-invitation-heading" class="text-lg">
                {{ __('forum_events.notifications.invitation_title') }}
            </h2>
            <p>
                {{ __('forum_events.labels.invited_by', ['name' => $this->currentInvitation['inviter_name']]) }}
                · {{ $this->currentInvitation['expires_at'] }}
            </p>
            <div class="mt-3 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="forum-button forum-button--primary min-h-11"
                    wire:click="respondToInvitation({{ $this->currentInvitation['id'] }}, true)"
                    wire:loading.attr="disabled"
                    wire:target="respondToInvitation"
                >
                    <x-lucide-check aria-hidden="true" />
                    {{ __('forum_events.actions.accept_invitation') }}
                </button>
                <button
                    type="button"
                    class="forum-button min-h-11"
                    wire:click="respondToInvitation({{ $this->currentInvitation['id'] }}, false)"
                    wire:loading.attr="disabled"
                    wire:target="respondToInvitation"
                >
                    <x-lucide-x aria-hidden="true" />
                    {{ __('forum_events.actions.decline_invitation') }}
                </button>
            </div>
        </section>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(18rem,0.8fr)]">
        <div class="grid min-w-0 gap-6">
            <section aria-labelledby="event-overview-heading">
                <h2 id="event-overview-heading">{{ __('forum_events.detail.overview') }}</h2>
                <dl class="mt-4 grid gap-4 border-y border-paw-line py-4 sm:grid-cols-2">
                    <div>
                        <dt class="font-semibold">{{ __('forum_events.fields.starts_at') }}</dt>
                        <dd>
                            <time
                                datetime="{{ $this->event['starts_at_iso'] }}"
                                aria-label="{{ $this->event['starts_at_accessible'] }}"
                            >
                                {{ $this->event['starts_at'] }}
                            </time>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold">{{ __('forum_events.fields.ends_at') }}</dt>
                        <dd>
                            <time
                                datetime="{{ $this->event['ends_at_iso'] }}"
                                aria-label="{{ $this->event['ends_at_accessible'] }}"
                            >
                                {{ $this->event['ends_at'] }}
                            </time>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold">{{ __('forum_events.fields.format') }}</dt>
                        <dd>{{ $this->event['format'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">{{ __('forum_events.fields.visibility') }}</dt>
                        <dd>{{ $this->event['visibility'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">{{ __('forum_events.fields.pet_participation_mode') }}</dt>
                        <dd>{{ $this->event['pet_participation'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">{{ __('forum_events.fields.event_version') }}</dt>
                        <dd>{{ $this->event['current_version_number'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">{{ __('forum_events.fields.registration_policy') }}</dt>
                        <dd>{{ $this->event['registration_policy'] }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold">{{ __('forum_events.fields.cost_minor') }}</dt>
                        <dd>{{ $this->event['cost'] }}</dd>
                    </div>
                    @if ($this->event['refund_policy'])
                        <div>
                            <dt class="font-semibold">{{ __('forum_events.fields.refund_policy') }}</dt>
                            <dd class="whitespace-pre-line">{{ $this->event['refund_policy'] }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="font-semibold">{{ __('forum_events.fields.photo_consent_mode') }}</dt>
                        <dd>{{ $this->event['photo_consent'] }}</dd>
                    </div>
                    @if ($this->event['group_url'])
                        <div>
                            <dt class="font-semibold">{{ __('forum_events.fields.club') }}</dt>
                            <dd>
                                <a class="text-link" href="{{ $this->event['group_url'] }}" wire:navigate>
                                    {{ $this->event['group_name'] }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if ($this->event['minimum_animal_age_months'] !== null)
                        <div>
                            <dt class="font-semibold">{{ __('forum_events.fields.minimum_animal_age_months') }}</dt>
                            <dd>{{ $this->event['minimum_animal_age_months'] }}</dd>
                        </div>
                    @endif
                    @if ($this->event['maximum_animal_age_months'] !== null)
                        <div>
                            <dt class="font-semibold">{{ __('forum_events.fields.maximum_animal_age_months') }}</dt>
                            <dd>{{ $this->event['maximum_animal_age_months'] }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="font-semibold">{{ __('forum_events.fields.capacity') }}</dt>
                        <dd>
                            @if ($this->event['capacity'] === null)
                                {{ __('forum_events.labels.unlimited_capacity', ['confirmed' => $this->event['confirmed_count']]) }}
                            @else
                                {{ __('forum_events.labels.capacity', ['confirmed' => $this->event['confirmed_count'], 'capacity' => $this->event['capacity']]) }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold">{{ trans_choice('forum_events.labels.waitlist', $this->event['waitlist_count'], ['count' => $this->event['waitlist_count']]) }}</dt>
                        <dd>{{ $this->event['waitlist_count'] }}</dd>
                    </div>
                </dl>

                @if ($this->event['taxa'] !== [])
                    <ul class="mt-4 flex flex-wrap gap-2" aria-label="{{ __('forum_events.fields.taxon_ids') }}">
                        @forelse ($this->event['taxa'] as $taxon)
                            <li class="forum-topic-card__tag">{{ $taxon['name'] }}</li>
                        @empty
                        @endforelse
                    </ul>
                @endif
            </section>

            <section aria-labelledby="event-requirements-heading">
                <h2 id="event-requirements-heading">{{ __('forum_events.detail.requirements') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @if ($this->event['attendance_requirements'])
                        <article class="border-s-4 border-paw-line ps-4">
                            <h3 class="text-base">{{ __('forum_events.fields.attendance_requirements') }}</h3>
                            <p class="whitespace-pre-line">{{ $this->event['attendance_requirements'] }}</p>
                        </article>
                    @endif
                    @if ($this->event['vaccination_requirements'])
                        <article class="border-s-4 border-paw-line ps-4">
                            <h3 class="text-base">{{ __('forum_events.fields.vaccination_requirements') }}</h3>
                            <p class="whitespace-pre-line">{{ $this->event['vaccination_requirements'] }}</p>
                            @if ($this->event['vaccination_jurisdiction'])
                                <p class="text-sm">{{ $this->event['vaccination_jurisdiction'] }}</p>
                            @endif
                        </article>
                    @endif
                    <article class="border-s-4 border-status-info ps-4">
                        <h3 class="inline-flex items-center gap-2 text-base">
                            <x-lucide-accessibility class="size-4" aria-hidden="true" />
                            {{ __('forum_events.detail.accessibility') }}
                        </h3>
                        <p><strong>{{ $this->event['accessibility_status'] }}</strong></p>
                        @if ($this->event['accessibility_information'])
                            <p class="whitespace-pre-line">{{ $this->event['accessibility_information'] }}</p>
                        @endif
                    </article>
                    <article class="border-s-4 border-status-success ps-4">
                        <h3 class="text-base">{{ __('forum_events.detail.welfare') }}</h3>
                        <p class="whitespace-pre-line">{{ $this->event['animal_welfare_rules'] }}</p>
                    </article>
                </div>
            </section>

            <section aria-labelledby="event-access-heading">
                <h2 id="event-access-heading">{{ __('forum_events.detail.schedule') }}</h2>
                <div class="mt-4 border-y border-paw-line py-4">
                    @if ($this->occurrences !== [])
                        <ol class="mb-4 grid gap-3" aria-label="{{ __('forum_events.detail.occurrences') }}">
                            @forelse ($this->occurrences as $occurrence)
                                <li class="border-s-4 border-paw-line ps-4" wire:key="event-occurrence-{{ $occurrence['id'] }}">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <strong>{{ $occurrence['starts_at'] }}</strong>
                                        <x-status-badge :label="$occurrence['status']" icon="calendar-clock" />
                                    </div>
                                    <p>{{ $occurrence['format'] }} · {{ $occurrence['location'] }}</p>
                                    <p class="text-sm">{{ __('forum_events.labels.occurrence_ends', ['date' => $occurrence['ends_at'], 'timezone' => $occurrence['timezone']]) }}</p>
                                    @if ($occurrence['is_override'])
                                        <p class="text-sm">{{ __('forum_events.labels.occurrence_override') }}</p>
                                    @endif
                                </li>
                            @empty
                            @endforelse
                        </ol>
                    @endif

                    <x-event-schedule
                        class="mb-5"
                        :days="$this->schedule"
                        :can-manage="$this->event['can_manage_schedule']"
                    />

                    @if ($this->event['location_scope'])
                        <p class="inline-flex items-start gap-2">
                            <x-lucide-map-pin class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            <span>{{ $this->event['location_scope'] }}</span>
                        </p>
                    @endif

                    @if ($this->event['can_view_access'])
                        @if ($this->event['exact_location'])
                            <p class="mt-3 whitespace-pre-line">{{ $this->event['exact_location'] }}</p>
                        @endif
                        @if ($this->event['online_url'])
                            <a
                                class="forum-button mt-3 min-h-11 justify-self-start"
                                href="{{ $this->event['online_url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <x-lucide-video aria-hidden="true" />
                                {{ __('forum_events.fields.online_url') }}
                            </a>
                        @endif
                        @if ($this->event['emergency_contact_plan'])
                            <div class="mt-4 border-s-4 border-status-danger ps-4">
                                <h3 class="text-base">{{ __('forum_events.fields.emergency_contact_plan') }}</h3>
                                <p class="whitespace-pre-line">{{ $this->event['emergency_contact_plan'] }}</p>
                            </div>
                        @endif
                    @else
                        <p class="border-s-4 border-status-info ps-4" role="note">
                            {{ __('forum_events.notices.private_access') }}
                        </p>
                    @endif
                </div>
            </section>

            <section aria-labelledby="event-updates-heading">
                <h2 id="event-updates-heading">{{ __('forum_events.detail.updates') }}</h2>
                <div class="mt-4 divide-y divide-paw-line border-y border-paw-line">
                    @forelse ($this->updates as $update)
                        <article class="py-4" wire:key="event-update-{{ $update['id'] }}">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-base">{{ $update['title'] }}</h3>
                                <span class="text-sm">{{ $update['published_at'] }}</span>
                            </div>
                            <p class="text-sm">{{ $update['type'] }} · {{ $update['audience'] }}</p>
                            <p class="whitespace-pre-line">{{ $update['body'] }}</p>
                        </article>
                    @empty
                        <p class="py-4">{{ __('forum_events.empty.updates') }}</p>
                    @endforelse
                </div>
            </section>

            @if ($this->event['can_send_message'])
                <section aria-labelledby="event-messages-heading">
                    <h2 id="event-messages-heading">{{ __('forum_events.detail.messages') }}</h2>
                    <div class="mt-4 divide-y divide-paw-line border-y border-paw-line">
                        @forelse ($this->messages as $message)
                            <article class="py-3" wire:key="event-message-{{ $message['id'] }}">
                                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                                    <strong>{{ $message['sender_name'] }}</strong>
                                    <span>{{ $message['sent_at'] }}</span>
                                </div>
                                <p class="text-sm">{{ $message['audience'] }}</p>
                                <p class="whitespace-pre-line">{{ $message['body'] }}</p>
                            </article>
                        @empty
                            <p class="py-4">{{ __('forum_events.empty.messages') }}</p>
                        @endforelse
                    </div>
                    <form wire:submit="sendMessage" class="forum-form mt-4 grid gap-3">
                        <label class="forum-form__field">
                            <span>{{ __('forum_events.fields.message_audience') }}</span>
                            <select wire:model="messageForm.audience" required>
                                @forelse ($this->messageAudienceOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @empty
                                @endforelse
                            </select>
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_events.fields.message_body') }}</span>
                            <textarea wire:model="messageForm.body" rows="3" maxlength="3000" required></textarea>
                            @error('messageForm.body') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <button type="submit" class="forum-button forum-button--primary min-h-11 justify-self-start" wire:loading.attr="disabled" wire:target="sendMessage">
                            <x-lucide-send aria-hidden="true" />
                            {{ __('forum_events.actions.send_message') }}
                        </button>
                    </form>
                </section>
            @endif

            <section aria-labelledby="event-reviews-heading">
                <h2 id="event-reviews-heading">{{ __('forum_events.detail.reviews') }}</h2>
                <div class="mt-4 divide-y divide-paw-line border-y border-paw-line">
                    @forelse ($this->reviews as $review)
                        <article class="py-4" wire:key="event-review-{{ $review['id'] }}">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-base">{{ $review['title'] }}</h3>
                                <span aria-label="{{ __('forum_events.fields.rating') }}">{{ $review['rating'] }}/5</span>
                            </div>
                            <p class="text-sm">
                                {{ __('forum_events.labels.review_by', ['name' => $review['reviewer_name'], 'date' => $review['created_at']]) }}
                            </p>
                            <p class="whitespace-pre-line">{{ $review['body'] }}</p>
                        </article>
                    @empty
                        <p class="py-4">{{ __('forum_events.empty.reviews') }}</p>
                    @endforelse
                </div>

                @if ($this->event['can_review'])
                    <form wire:submit="submitReview" class="forum-form mt-4 grid gap-3">
                        <label class="forum-form__field">
                            <span>{{ __('forum_events.fields.rating') }}</span>
                            <input type="number" wire:model="reviewForm.rating" min="1" max="5" required>
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_events.fields.title') }}</span>
                            <input type="text" wire:model="reviewForm.title" minlength="4" maxlength="180" required>
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_events.fields.review_body') }}</span>
                            <textarea wire:model="reviewForm.body" rows="4" minlength="10" maxlength="5000" required></textarea>
                            @error('reviewForm.body') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <button type="submit" class="forum-button forum-button--primary min-h-11 justify-self-start" wire:loading.attr="disabled" wire:target="submitReview">
                            <x-lucide-star aria-hidden="true" />
                            {{ __('forum_events.actions.submit_review') }}
                        </button>
                    </form>
                @endif
            </section>
        </div>

        <aside class="grid content-start gap-5">
            @if ($this->currentRegistration)
                <section class="forum-form" aria-labelledby="event-registration-heading">
                    <h2 id="event-registration-heading" class="text-lg">{{ __('forum_events.actions.register') }}</h2>
                    <p><strong>{{ $this->currentRegistration['status'] }}</strong></p>
                    <p>{{ $this->currentRegistration['attendance_format'] }}</p>
                    @if ($this->currentRegistration['occurrence'])
                        <p>{{ __('forum_events.labels.registered_occurrence', ['date' => $this->currentRegistration['occurrence']]) }}</p>
                    @endif
                    @if ($this->currentRegistration['event_version'])
                        <p class="text-sm">{{ __('forum_events.labels.accepted_version', ['version' => $this->currentRegistration['event_version']]) }}</p>
                    @endif
                    @if ($this->currentRegistration['pets'] !== [])
                        <ul class="grid gap-1 text-sm" aria-label="{{ __('forum_events.fields.pet_profiles') }}">
                            @forelse ($this->currentRegistration['pets'] as $pet)
                                <li>{{ $pet['name'] }} · {{ $pet['species'] }} · {{ $pet['eligibility'] }}</li>
                            @empty
                            @endforelse
                        </ul>
                    @endif
                    @if ($this->currentRegistration['waitlist_position'])
                        <p>{{ __('forum_events.labels.waitlist_position', ['position' => $this->currentRegistration['waitlist_position']]) }}</p>
                    @endif
                    @if ($this->currentRegistration['can_cancel'])
                        <button
                            type="button"
                            class="forum-button forum-button--danger min-h-11"
                            wire:click="cancelRegistration"
                            wire:confirm="{{ __('forum_events.actions.cancel_registration_confirm') }}"
                            wire:loading.attr="disabled"
                            wire:target="cancelRegistration"
                        >
                            <x-lucide-calendar-x aria-hidden="true" />
                            {{ __('forum_events.actions.cancel_registration') }}
                        </button>
                    @endif
                </section>
            @elseif ($this->event['can_register'])
                <form wire:submit="register" class="forum-form grid gap-3">
                    <h2 class="text-lg">{{ __('forum_events.actions.register') }}</h2>
                    @if ($this->event['cost'] !== __('forum_events.labels.cost_free'))
                        <p class="border-s-4 border-status-warning ps-4">{{ __('forum_events.notices.payment_unavailable') }}</p>
                    @endif
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.attendance_format') }}</span>
                        <select wire:model="registrationForm.attendanceFormat" required>
                            @forelse ($this->attendanceFormatOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                            @endforelse
                        </select>
                    </label>
                    @if ($this->occurrences !== [])
                        <label class="forum-form__field">
                            <span>{{ __('forum_events.fields.occurrence') }}</span>
                            <select wire:model="registrationForm.occurrenceId" required>
                                @forelse ($this->occurrences as $occurrence)
                                    <option value="{{ $occurrence['id'] }}">{{ $occurrence['starts_at'] }} · {{ $occurrence['status'] }}</option>
                                @empty
                                @endforelse
                            </select>
                            @error('registrationForm.occurrenceId') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    @endif
                    @if ($this->event['accepts_general_pets'])
                        <fieldset class="forum-form__field">
                            <legend>{{ __('forum_events.fields.pet_profiles') }}</legend>
                            <div class="grid gap-2">
                                @forelse ($this->petOptions as $value => $label)
                                    <label class="inline-flex min-h-11 items-center gap-3">
                                        <input type="checkbox" wire:model="registrationForm.petProfileIds" value="{{ $value }}">
                                        <span>{{ $label }}</span>
                                    </label>
                                @empty
                                    <p>{{ __('forum_events.empty.pets') }}</p>
                                @endforelse
                            </div>
                            @if ($this->event['requires_pet'])
                                <p class="text-sm">{{ __('forum_events.notices.pet_required') }}</p>
                            @endif
                            @error('registrationForm.petProfileIds') <small role="alert">{{ $message }}</small> @enderror
                            @error('registrationForm.petProfileIds.*') <small role="alert">{{ $message }}</small> @enderror
                        </fieldset>
                    @else
                        <p class="border-s-4 border-status-info ps-4" role="note">
                            {{ $this->event['pet_participation'] }}
                        </p>
                    @endif
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.guest_count') }}</span>
                        <input type="number" wire:model="registrationForm.guestCount" min="0" max="10" required>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.photo_consent_mode') }}</span>
                        <select wire:model="registrationForm.photoConsent" required>
                            @forelse ($this->photoConsentOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                            @endforelse
                        </select>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_events.fields.requirements_note') }}</span>
                        <textarea wire:model="registrationForm.requirementsNote" rows="3" maxlength="3000"></textarea>
                    </label>
                    <label class="inline-flex min-h-11 items-start gap-3">
                        <input class="mt-1" type="checkbox" wire:model="registrationForm.requirementsAccepted" required>
                        <span>{{ __('forum_events.fields.requirements_accepted') }}</span>
                    </label>
                    <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="register">
                        <x-lucide-calendar-check aria-hidden="true" />
                        <span wire:loading.remove wire:target="register">{{ __('forum_events.actions.register') }}</span>
                        <span wire:loading wire:target="register">{{ __('forum_events.actions.registering') }}</span>
                    </button>
                </form>
            @endif

            @if ($this->event['can_manage_schedule'])
                <section class="forum-form" aria-labelledby="event-schedule-management-heading">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 id="event-schedule-management-heading" class="text-lg">{{ __('forum_events.schedule.manage_heading') }}</h2>
                            <p>{{ __('forum_events.schedule.manage_description') }}</p>
                        </div>
                        @if ($editingSessionId)
                            <button type="button" class="forum-button min-h-11" wire:click="resetSessionEditor">
                                <x-lucide-plus aria-hidden="true" />
                                {{ __('forum_events.actions.new_session') }}
                            </button>
                        @endif
                    </div>

                    <form wire:submit="saveSession" class="mt-4 grid gap-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.session_occurrence') }}</span>
                                <select wire:model="sessionForm.occurrenceId" required>
                                    @forelse ($this->scheduleOccurrenceOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('sessionForm.occurrenceId') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.session_title') }}</span>
                                <input type="text" wire:model="sessionForm.title" minlength="3" maxlength="180" required>
                                @error('sessionForm.title') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.session_type') }}</span>
                                <select wire:model="sessionForm.type" required>
                                    @forelse ($this->sessionTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('sessionForm.type') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.session_status') }}</span>
                                <select wire:model="sessionForm.status" required>
                                    @forelse ($this->sessionStatusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('sessionForm.status') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.starts_at') }}</span>
                                <input type="datetime-local" wire:model="sessionForm.startsAt" required>
                                @error('sessionForm.startsAt') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.ends_at') }}</span>
                                <input type="datetime-local" wire:model="sessionForm.endsAt" required>
                                @error('sessionForm.endsAt') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.timezone') }}</span>
                                <input type="text" wire:model="sessionForm.timezone" maxlength="64" required>
                                @error('sessionForm.timezone') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.capacity') }}</span>
                                <input type="number" wire:model="sessionForm.capacity" min="1" max="100000">
                                @error('sessionForm.capacity') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.session_track') }}</span>
                                <select wire:model="sessionForm.trackId">
                                    <option value="">{{ __('forum_events.schedule.no_track') }}</option>
                                    @forelse ($this->scheduleTrackOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('sessionForm.trackId') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.session_room') }}</span>
                                <select wire:model="sessionForm.roomId">
                                    <option value="">{{ __('forum_events.schedule.no_room') }}</option>
                                    @forelse ($this->scheduleRoomOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('sessionForm.roomId') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.session_reservation_policy') }}</span>
                                <select wire:model="sessionForm.reservationPolicy" required>
                                    @forelse ($this->sessionReservationPolicyOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('sessionForm.reservationPolicy') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.session_position') }}</span>
                                <input type="number" wire:model="sessionForm.position" min="0" max="65535" required>
                                @error('sessionForm.position') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.session_staff') }}</span>
                                <select wire:model="sessionForm.staffUserId">
                                    <option value="">{{ __('forum_events.schedule.no_staff') }}</option>
                                    @forelse ($this->scheduleStaffOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('sessionForm.staffUserId') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.session_staff_role') }}</span>
                                <select wire:model="sessionForm.staffRole" required>
                                    @forelse ($this->sessionRoleOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('sessionForm.staffRole') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                        </div>

                        <label class="forum-form__field">
                            <span>{{ __('forum_events.fields.session_summary') }}</span>
                            <textarea wire:model="sessionForm.summary" rows="3" maxlength="5000"></textarea>
                            @error('sessionForm.summary') <small role="alert">{{ $message }}</small> @enderror
                        </label>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="inline-flex min-h-11 items-start gap-3">
                                <input class="mt-1" type="checkbox" wire:model="sessionForm.isRequired">
                                <span>{{ __('forum_events.fields.session_required') }}</span>
                                @error('sessionForm.isRequired') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="inline-flex min-h-11 items-start gap-3">
                                <input class="mt-1" type="checkbox" wire:model="sessionForm.staffIsPublic">
                                <span>{{ __('forum_events.fields.session_staff_public') }}</span>
                                @error('sessionForm.staffIsPublic') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                        </div>

                        @if ($this->event['can_override_schedule_conflict'])
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.session_conflict_override') }}</span>
                                <textarea wire:model="sessionForm.conflictOverrideReason" rows="3" minlength="20" maxlength="2000"></textarea>
                                <small>{{ __('forum_events.schedule.override_help') }}</small>
                            </label>
                        @endif

                        @error('sessionForm.conflictOverrideReason') <p class="border-s-4 border-status-danger ps-4" role="alert">{{ $message }}</p> @enderror
                        @error('sessionForm') <p class="border-s-4 border-status-danger ps-4" role="alert">{{ $message }}</p> @enderror

                        <button type="submit" class="forum-button forum-button--primary min-h-11 justify-self-start" wire:loading.attr="disabled" wire:target="saveSession">
                            <x-lucide-calendar-plus aria-hidden="true" />
                            <span wire:loading.remove wire:target="saveSession">
                                {{ $editingSessionId ? __('forum_events.actions.update_session') : __('forum_events.actions.create_session') }}
                            </span>
                            <span wire:loading wire:target="saveSession">{{ __('forum_events.actions.saving_session') }}</span>
                        </button>
                    </form>
                </section>
            @endif

            @if ($this->event['can_update'])
                <section class="forum-form" aria-labelledby="event-management-heading">
                    <h2 id="event-management-heading" class="text-lg">{{ __('forum_events.detail.management') }}</h2>

                    @if ($this->event['can_invite'])
                        <details>
                            <summary class="forum-button min-h-11">
                                <x-lucide-user-round-plus aria-hidden="true" />
                                {{ __('forum_events.actions.invite') }}
                            </summary>
                            <form wire:submit="invite" class="mt-3 grid gap-3">
                                <label class="forum-form__field">
                                    <span>{{ __('forum_events.fields.recipient_email') }}</span>
                                    <input type="email" wire:model="invitationForm.recipientEmail" maxlength="255" required>
                                </label>
                                <label class="forum-form__field">
                                    <span>{{ __('forum_events.fields.invitation_expires_at') }}</span>
                                    <input type="datetime-local" wire:model="invitationForm.expiresAt" required>
                                </label>
                                <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="invite">
                                    <x-lucide-send aria-hidden="true" />
                                    {{ __('forum_events.actions.invite') }}
                                </button>
                            </form>
                        </details>
                    @endif

                    <details class="mt-3">
                        <summary class="forum-button min-h-11">
                            <x-lucide-megaphone aria-hidden="true" />
                            {{ __('forum_events.actions.publish_update') }}
                        </summary>
                        <form wire:submit="publishUpdate" class="mt-3 grid gap-3">
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.update_type') }}</span>
                                <select wire:model="updateForm.type" required>
                                    @forelse ($this->updateTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.update_audience') }}</span>
                                <select wire:model="updateForm.audience" required>
                                    @forelse ($this->updateAudienceOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.update_title') }}</span>
                                <input type="text" wire:model="updateForm.title" minlength="4" maxlength="180" required>
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_events.fields.update_body') }}</span>
                                <textarea wire:model="updateForm.body" rows="4" minlength="10" maxlength="10000" required></textarea>
                            </label>
                            <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="publishUpdate">
                                <x-lucide-megaphone aria-hidden="true" />
                                {{ __('forum_events.actions.publish_update') }}
                            </button>
                        </form>
                    </details>

                    @if ($this->event['status_key'] === 'scheduled')
                        <details class="mt-3">
                            <summary class="forum-button min-h-11">
                                <x-lucide-calendar-clock aria-hidden="true" />
                                {{ __('forum_events.actions.reschedule_event') }}
                            </summary>
                            <form wire:submit="reschedule" class="mt-3 grid gap-3">
                                <label class="forum-form__field">
                                    <span>{{ __('forum_events.fields.starts_at') }}</span>
                                    <input type="datetime-local" wire:model="rescheduleForm.startsAt" required>
                                </label>
                                <label class="forum-form__field">
                                    <span>{{ __('forum_events.fields.ends_at') }}</span>
                                    <input type="datetime-local" wire:model="rescheduleForm.endsAt" required>
                                </label>
                                <label class="forum-form__field">
                                    <span>{{ __('forum_events.fields.timezone') }}</span>
                                    <input type="text" wire:model="rescheduleForm.timezone" maxlength="64" required>
                                </label>
                                <label class="forum-form__field">
                                    <span>{{ __('forum_events.fields.reschedule_reason') }}</span>
                                    <textarea wire:model="rescheduleForm.explanation" rows="3" minlength="10" maxlength="5000" required></textarea>
                                </label>
                                <button type="submit" class="forum-button min-h-11" wire:loading.attr="disabled" wire:target="reschedule">
                                    <x-lucide-calendar-clock aria-hidden="true" />
                                    {{ __('forum_events.actions.reschedule_event') }}
                                </button>
                            </form>
                        </details>

                        <details class="mt-3">
                            <summary class="forum-button forum-button--danger min-h-11">
                                <x-lucide-calendar-x aria-hidden="true" />
                                {{ __('forum_events.actions.cancel_event') }}
                            </summary>
                            <form wire:submit="cancelEvent" class="mt-3 grid gap-3">
                                <label class="forum-form__field">
                                    <span>{{ __('forum_events.fields.cancellation_reason') }}</span>
                                    <textarea wire:model="cancellationExplanation" rows="3" minlength="10" maxlength="5000" required></textarea>
                                </label>
                                <button
                                    type="submit"
                                    class="forum-button forum-button--danger min-h-11"
                                    wire:confirm="{{ __('forum_events.actions.cancel_event_confirm') }}"
                                    wire:loading.attr="disabled"
                                    wire:target="cancelEvent"
                                >
                                    <x-lucide-calendar-x aria-hidden="true" />
                                    {{ __('forum_events.actions.cancel_event') }}
                                </button>
                            </form>
                        </details>
                    @endif
                </section>
            @endif

            @if ($this->event['can_manage_registrations'])
                <section class="forum-form" aria-labelledby="event-attendees-heading">
                    <h2 id="event-attendees-heading" class="text-lg">{{ __('forum_events.detail.attendees') }}</h2>
                    <div class="divide-y divide-paw-line">
                        @forelse ($this->registrations as $registration)
                            <article class="py-3" wire:key="event-registration-{{ $registration['id'] }}">
                                <strong>{{ $registration['user_name'] }}</strong>
                                <p class="break-all text-sm">{{ $registration['user_email'] }}</p>
                                <p class="text-sm">{{ $registration['status'] }} · {{ $registration['attendance_format'] }}</p>
                                @if ($registration['occurrence'])
                                    <p class="text-sm">{{ __('forum_events.labels.registered_occurrence', ['date' => $registration['occurrence']]) }}</p>
                                @endif
                                @if ($registration['event_version'])
                                    <p class="text-sm">{{ __('forum_events.labels.accepted_version', ['version' => $registration['event_version']]) }}</p>
                                @endif
                                @if ($registration['pets'] !== [])
                                    <ul class="grid gap-1 text-sm" aria-label="{{ __('forum_events.fields.pet_profiles') }}">
                                        @forelse ($registration['pets'] as $pet)
                                            <li>{{ $pet['name'] }} · {{ $pet['species'] }} · {{ $pet['eligibility'] }}</li>
                                        @empty
                                        @endforelse
                                    </ul>
                                @elseif ($registration['pet_name'])
                                    <p class="text-sm">{{ $registration['pet_name'] }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @if ($registration['status_key'] === 'pending')
                                        <button type="button" class="forum-button min-h-11" wire:click="reviewRegistration({{ $registration['id'] }}, true)">
                                            <x-lucide-check aria-hidden="true" />
                                            {{ __('forum_events.actions.approve') }}
                                        </button>
                                        <button type="button" class="forum-button min-h-11" wire:click="reviewRegistration({{ $registration['id'] }}, false)">
                                            <x-lucide-x aria-hidden="true" />
                                            {{ __('forum_events.actions.decline') }}
                                        </button>
                                    @elseif ($registration['status_key'] === 'confirmed')
                                        <button type="button" class="forum-button min-h-11" wire:click="checkIn({{ $registration['id'] }})">
                                            <x-lucide-badge-check aria-hidden="true" />
                                            {{ __('forum_events.actions.check_in') }}
                                        </button>
                                    @elseif ($registration['status_key'] === 'checked_in')
                                        <button type="button" class="forum-button min-h-11" wire:click="checkOut({{ $registration['id'] }})">
                                            <x-lucide-log-out aria-hidden="true" />
                                            {{ __('forum_events.actions.check_out') }}
                                        </button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p>{{ __('forum_events.empty.registrations') }}</p>
                        @endforelse
                    </div>
                </section>
            @endif

            @if ($this->event['can_report'])
                <details class="forum-form">
                    <summary class="forum-button min-h-11">
                        <x-lucide-flag aria-hidden="true" />
                        {{ __('forum_events.actions.report') }}
                    </summary>
                    <form wire:submit="report" class="mt-3 grid gap-3">
                        <label class="forum-form__field">
                            <span>{{ __('forum_events.fields.report_reason') }}</span>
                            <select wire:model="reportForm.reason" required>
                                <option value="">{{ __('forum_events.fields.report_reason') }}</option>
                                @forelse ($this->reportReasonOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @empty
                                @endforelse
                            </select>
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_events.fields.report_description') }}</span>
                            <textarea wire:model="reportForm.description" rows="4" maxlength="1200"></textarea>
                        </label>
                        <label class="inline-flex min-h-11 items-start gap-3">
                            <input class="mt-1" type="checkbox" wire:model="reportForm.immediateSafety">
                            <span>{{ __('forum_moderation.forms.immediate_safety') }}</span>
                        </label>
                        <label class="inline-flex min-h-11 items-start gap-3">
                            <input class="mt-1" type="checkbox" wire:model="reportForm.truthfulnessConfirmed" required>
                            <span>{{ __('forum_moderation.forms.truthfulness') }}</span>
                        </label>
                        <button type="submit" class="forum-button min-h-11" wire:loading.attr="disabled" wire:target="report">
                            <x-lucide-flag aria-hidden="true" />
                            {{ __('forum_events.actions.report') }}
                        </button>
                    </form>
                </details>
            @endif
        </aside>
    </div>
</section>
