@props(['event', 'registration'])

<x-ui.content-panel
    section="event-registration"
    eyebrow="Your place"
    title="Registration and ticket"
    :meta="$registration['status_label']"
    class="event-registration"
>
    @php($record = $registration['registration'])

    @if ($event['status'] === 'cancelled')
        <x-ui.notice
            icon="calendar-x"
            title="This event was cancelled"
            description="New registration and payments are closed. Existing obligations remain visible to affected attendees."
            class="section-body"
        />
    @elseif ($event['registration_policy'] === 'invitation' && $record === null)
        <x-ui.notice
            icon="lock-keyhole"
            title="Invitation required"
            description="This private event accepts only profiles selected by the organizer."
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
                    <p class="event-directory__empty">Ticket options are unavailable.</p>
                @endforelse
            </div>

            <div class="event-registration__fields">
                <label class="form-field">
                    <span class="form-field__label">Attending profile</span>
                    <select name="event_pet" class="field field--select" required>
                        @foreach ($registration['pets'] as $value => $label)
                            <option value="{{ $value }}" @selected(! $registration['can_register_pet'] && $value === 'owner-only')>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-field">
                    <span class="form-field__label">Attendance format</span>
                    <select name="attendance_format" class="field field--select" required>
                        <option value="{{ $event['format'] }}">{{ \Illuminate\Support\Str::headline($event['format']) }}</option>
                    </select>
                </label>
                <label class="form-field">
                    <span class="form-field__label">Guests</span>
                    <input type="number" name="guest_count" value="0" min="0" max="5" class="field">
                </label>
                <label class="form-field">
                    <span class="form-field__label">Photography</span>
                    <select name="photo_consent" class="field field--select" required>
                        <option value="ask-first">Ask before publishing</option>
                        <option value="yes">Photography is okay</option>
                        <option value="no">No photos or tags</option>
                    </select>
                </label>
            </div>

            <label class="form-field">
                <span class="form-field__label">Private note for organizers</span>
                <textarea name="requirements_note" rows="3" maxlength="500" class="field field--textarea" placeholder="Accessibility, distance, or arrival needs"></textarea>
            </label>

            <label class="event-registration__consent">
                <input type="checkbox" name="accepted_rules" value="yes" required>
                <span>I reviewed the event rules, cancellation terms, and pet-safety boundaries.</span>
            </label>

            <p class="event-registration__terms">{{ $registration['terms'] }}</p>
            <x-ui.action-control
                type="submit"
                :label="$event['registration_policy'] === 'approval' ? 'Send application' : 'Confirm registration'"
                icon="ticket-check"
                variant="primary"
                size="regular"
            />
        </form>
    @else
        <div class="event-registration__record section-body">
            <x-ui.notice
                :icon="match ($record['status']) {
                    'confirmed', 'checked_in' => 'badge-check',
                    'payment_required', 'payment_failed' => 'credit-card',
                    'waitlisted' => 'list-ordered',
                    default => 'clock-3',
                }"
                :title="$registration['status_label']"
                :description="match ($record['status']) {
                    'pending' => 'The organizer is reviewing the selected pet and private participation note.',
                    'waitlisted' => 'Your place will not be charged until a real opening is offered.',
                    'payment_required' => 'Your place is reserved temporarily. Complete the prototype payment to issue a ticket.',
                    'payment_failed' => 'No charge was created. You can retry without creating a duplicate registration.',
                    'checked_in' => 'Attendance is confirmed and this ticket cannot be checked in twice.',
                    default => 'Your place and event access are confirmed.',
                }"
            />

            @if (in_array($record['status'], ['payment_required', 'payment_failed'], true))
                <div class="event-registration__payment">
                    <div>
                        <span>Prototype payment</span>
                        <strong>{{ $event['price_label'] }}</strong>
                        <small>No payment credentials are collected on this preview.</small>
                    </div>
                    <x-ui.action-control
                        label="Complete payment"
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
                    <x-ui.action-control
                        label="Simulate failure"
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
                        <small>Ticket code</small>
                        <strong>{{ $record['ticket_code'] }}</strong>
                        <span>{{ \Illuminate\Support\Str::headline($record['ticket_type']) }} · {{ $record['pet'] }}</span>
                    </div>
                </div>
            @endif

            <div class="event-registration__actions">
                @if ($registration['check_in_action'])
                    <x-ui.action-control
                        :label="$registration['check_in_action']['label']"
                        :icon="$registration['check_in_action']['icon']"
                        :endpoint="$registration['check_in_action']['endpoint']"
                        :payload="$registration['check_in_action']['payload']"
                        :active="$registration['check_in_action']['active']"
                        variant="primary"
                    />
                @endif
                <x-ui.action-control
                    :label="$registration['calendar_action']['label']"
                    :icon="$registration['calendar_action']['icon']"
                    :endpoint="$registration['calendar_action']['endpoint']"
                    :payload="$registration['calendar_action']['payload']"
                    :active="$registration['calendar_action']['active']"
                    variant="paper"
                />
                <x-ui.action-control
                    :label="$registration['reminder_action']['label']"
                    :icon="$registration['reminder_action']['icon']"
                    :endpoint="$registration['reminder_action']['endpoint']"
                    :payload="$registration['reminder_action']['payload']"
                    :active="$registration['reminder_action']['active']"
                    variant="paper"
                />
                @if ($registration['cancel_action'])
                    <x-ui.action-control
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
                    <label for="travel-status" class="form-field__label">Arrival status</label>
                    <select id="travel-status" name="travel_status" class="field field--select" required>
                        @foreach ([
                            'leaving' => 'Leaving now',
                            'approaching' => 'Approaching',
                            'late' => 'Running late',
                            'arrived' => 'At the meeting point',
                            'cannot-find' => 'Cannot find the entrance',
                            'not-coming' => 'Not coming',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected($registration['travel_status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-ui.action-control type="submit" label="Update" icon="navigation" variant="paper" />
                </form>
            @endif
        </div>
    @endif
</x-ui.content-panel>
