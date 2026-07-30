<x-content-panel
    section="event-registration"
    eyebrow="{{ __('ui.your_place_cc9cdfb43b') }}"
    title="{{ __('ui.registration_and_ticket_1b0d0107d0') }}"
    :meta="$registration['status_label']"
    class="event-registration"
>
    @if ($event['status'] === 'cancelled')
        <x-notice
            icon="calendar-x"
            title="{{ __('ui.this_event_was_cancelled_ae1a6acc0c') }}"
            description="{{ __('ui.new_registration_and_payments_are_closed_existing_obligations_196d67445d') }}"
            class="section-body"
        />
    @elseif ($event['registration_policy'] === 'invitation' && $record === null)
        <x-notice
            icon="lock-keyhole"
            title="{{ __('ui.invitation_required_771b7240e7') }}"
            description="{{ __('ui.this_private_event_accepts_only_profiles_selected_by_f11623d75a') }}"
            class="section-body"
        />
    @elseif ($record === null || in_array($record['status'], ['cancelled', 'declined'], true))
        <form method="POST" action="{{ $registration['register_action'] }}" class="event-registration__form section-body">
            @csrf
            @foreach ($registration['register_payload'] as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach

            <div class="event-registration__ticket-options">
                @forelse ($registration['ticket_options'] as $ticket)
                    <label class="event-ticket-option">
                        <input type="radio" name="ticket_type" value="{{ $ticket['key'] }}" @checked($loop->first)>
                        <span>
                            <strong>{{ $ticket['title'] }}</strong>
                            <small>{{ $ticket['description'] }}</small>
                        </span>
                        <b>{{ $ticket['price_label'] }}</b>
                    </label>
                @empty
                    <p class="event-directory__empty">{{ __('ui.ticket_options_are_unavailable_c1564147ab') }}</p>
                @endforelse
            </div>

            <div class="event-registration__fields">
                <label class="form-field">
                    <span class="form-field__label">{{ __('ui.attending_profile_5da031a1d1') }}</span>
                    <select name="event_pet" class="field field--select" required>
                        @foreach ($registration['pets'] as $value => $label)
                            <option value="{{ $value }}" @selected(! $registration['can_register_pet'] && $value === 'owner-only')>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-field">
                    <span class="form-field__label">{{ __('ui.attendance_format_92ed405299') }}</span>
                    <select name="attendance_format" class="field field--select" required>
                        <option value="{{ $event['format'] }}">{{ $event['format_label'] }}</option>
                    </select>
                </label>
                <label class="form-field">
                    <span class="form-field__label">{{ __('ui.guests_3c8e0fde6f') }}</span>
                    <input type="number" name="guest_count" value="0" min="0" max="5" class="field">
                </label>
                <label class="form-field">
                    <span class="form-field__label">{{ __('ui.photography_7be8b75c22') }}</span>
                    <select name="photo_consent" class="field field--select" required>
                        <option value="ask-first">{{ __('ui.ask_before_publishing_075ca9bb33') }}</option>
                        <option value="yes">{{ __('ui.photography_is_okay_b0c8f552d3') }}</option>
                        <option value="no">{{ __('ui.no_photos_or_tags_2f4d16dbfd') }}</option>
                    </select>
                </label>
            </div>

            <label class="form-field">
                <span class="form-field__label">{{ __('ui.private_note_for_organizers_2f8a9f7b89') }}</span>
                <textarea name="requirements_note" rows="3" maxlength="500" class="field field--textarea" placeholder="{{ __('ui.accessibility_distance_or_arrival_needs_a062e3cb89') }}"></textarea>
            </label>

            <label class="event-registration__consent">
                <input type="checkbox" name="accepted_rules" value="yes" required>
                <span>{{ __('ui.i_reviewed_the_event_rules_cancellation_terms_and_432528dacb') }}</span>
            </label>

            <p class="event-registration__terms">{{ $registration['terms'] }}</p>
            <x-action-control
                type="submit"
                :label="$event['registration_policy'] === 'approval' ? __('ui.send_application_174b538fa4') : __('ui.confirm_registration_6218dc7b18')"
                icon="ticket-check"
                variant="primary"
                size="regular"
            />
        </form>
    @else
        <div class="event-registration__record section-body">
            <x-notice
                :icon="match ($record['status']) {
                    'confirmed', 'checked_in' => 'badge-check',
                    'payment_required', 'payment_failed' => 'credit-card',
                    'waitlisted' => 'list-ordered',
                    default => 'clock-3',
                }"
                :title="$registration['status_label']"
                :description="match ($record['status']) {
                    'pending' => __('ui.the_organizer_is_reviewing_the_selected_pet_and_862a4b2dc1'),
                    'waitlisted' => __('ui.your_place_will_not_be_charged_until_a_d2a8005d31'),
                    'payment_required' => __('ui.your_place_is_reserved_temporarily_complete_the_prototype_3925be51fa'),
                    'payment_failed' => __('ui.no_charge_was_created_you_can_retry_without_06cbc80c5f'),
                    'checked_in' => __('ui.attendance_is_confirmed_and_this_ticket_cannot_be_3ac174f366'),
                    default => __('ui.your_place_and_event_access_are_confirmed_a0bb26a9f5'),
                }"
            />

            @if (in_array($record['status'], ['payment_required', 'payment_failed'], true))
                <div class="event-registration__payment">
                    <div>
                        <span>{{ __('ui.prototype_payment_c7ec07f196') }}</span>
                        <strong>{{ $event['price_label'] }}</strong>
                        <small>{{ __('ui.no_payment_credentials_are_collected_on_this_preview_66d30647ed') }}</small>
                    </div>
                    <x-action-control
                        label="{{ __('ui.complete_payment_c030632f07') }}"
                        icon="credit-card"
                        :endpoint="route('actions.perform')"
                        :payload="[
                            'action' => 'complete-event-payment',
                            'target' => $event['key'],
                            'payment_outcome' => 'success',
                            'event_return_tab' => 'tickets',
                        ]"
                        variant="primary"
                        size="regular"
                    />
                    <x-action-control
                        label="{{ __('ui.simulate_failure_b44d3cb37c') }}"
                        icon="triangle-alert"
                        :endpoint="route('actions.perform')"
                        :payload="[
                            'action' => 'complete-event-payment',
                            'target' => $event['key'],
                            'payment_outcome' => 'failure',
                            'event_return_tab' => 'tickets',
                        ]"
                        variant="paper"
                        size="regular"
                    />
                </div>
            @endif

            @if ($record['ticket_code'])
                <div class="event-ticket">
                    <span class="event-ticket__qr" aria-hidden="true">
                        <x-lucide-qr-code class="icon" />
                    </span>
                    <div>
                        <small>{{ __('ui.ticket_code_68067daecb') }}</small>
                        <strong>{{ $record['ticket_code'] }}</strong>
                        <span>{{ $record['ticket_type_label'] }} · {{ $record['pet'] }}</span>
                    </div>
                </div>
            @endif

            <div class="event-registration__actions">
                @if ($registration['check_in_action'])
                    <x-action-control
                        :label="$registration['check_in_action']['label']"
                        :icon="$registration['check_in_action']['icon']"
                        :endpoint="$registration['check_in_action']['endpoint']"
                        :payload="$registration['check_in_action']['payload']"
                        :active="$registration['check_in_action']['active']"
                        variant="primary"
                    />
                @endif
                <x-action-control
                    :label="$registration['calendar_action']['label']"
                    :icon="$registration['calendar_action']['icon']"
                    :endpoint="$registration['calendar_action']['endpoint']"
                    :payload="$registration['calendar_action']['payload']"
                    :active="$registration['calendar_action']['active']"
                    variant="paper"
                />
                <x-action-control
                    :label="$registration['reminder_action']['label']"
                    :icon="$registration['reminder_action']['icon']"
                    :endpoint="$registration['reminder_action']['endpoint']"
                    :payload="$registration['reminder_action']['payload']"
                    :active="$registration['reminder_action']['active']"
                    variant="paper"
                />
                @if ($registration['cancel_action'])
                    <x-action-control
                        :label="$registration['cancel_action']['label']"
                        :icon="$registration['cancel_action']['icon']"
                        :endpoint="$registration['cancel_action']['endpoint']"
                        :payload="$registration['cancel_action']['payload']"
                        variant="paper"
                    />
                @endif
            </div>

            @if (in_array($record['status'], ['confirmed', 'checked_in'], true) && $event['format'] !== 'online')
                <form method="POST" action="{{ route('actions.perform') }}" class="event-registration__travel">
                    @csrf
                    <input type="hidden" name="action" value="set-event-travel-status">
                    <input type="hidden" name="target" value="{{ $event['key'] }}">
                    <input type="hidden" name="event_return_tab" value="tickets">
                    <label for="travel-status" class="form-field__label">{{ __('ui.arrival_status_45373f71f9') }}</label>
                    <select id="travel-status" name="travel_status" class="field field--select" required>
                        @foreach ([
                            'leaving' => __('ui.leaving_now_13338e1c94'),
                            'approaching' => __('ui.approaching_8b228f5424'),
                            'late' => __('ui.running_late_5d9471f0b6'),
                            'arrived' => __('ui.at_the_meeting_point_1aaac4b56f'),
                            'cannot-find' => __('ui.cannot_find_the_entrance_a5048a6c7c'),
                            'not-coming' => __('ui.not_coming_2fc32e45d9'),
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected($registration['travel_status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-action-control type="submit" label="{{ __('ui.update_c1c1009d3f') }}" icon="navigation" variant="paper" />
                </form>
            @endif
        </div>
    @endif
</x-content-panel>
