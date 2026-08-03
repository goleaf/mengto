@props(['events', 'device' => null, 'shared' => false])

<div class="device-event-list">
    @forelse ($events as $event)
        <article class="device-event device-event--{{ $event['severity'] }}">
            <div class="device-event__icon">
                <x-ui-icon :name="$event['severity'] === 'routine' ? 'info' : 'triangle-alert'" size="lg" />
            </div>
            <div class="device-event__body">
                <div class="device-event__heading">
                    <div>
                        <p>{{ $event['severity_label'] }} · {{ $event['pet_name'] }}</p>
                        <h3>{{ $event['title'] }}</h3>
                    </div>
                    <x-status-badge :label="$event['status_label']" :tone="$event['severity_tone']" />
                </div>
                <p class="device-event__summary">{{ $event['summary'] }}</p>
                <div class="device-event__meta">
                    <span>{{ $event['occurred_at'] }}</span>
                    <span>{{ $event['confidence'] }}</span>
                    <span>{{ $event['source'] }}</span>
                    @if ($event['occurrence_count'] > 1)
                        <span>{{ $event['occurrence_label'] }} · {{ $event['first_occurred_at'] }} – {{ $event['last_occurred_at'] }}</span>
                    @endif
                </div>
                @unless ($shared)
                    <div class="device-event__actions">
                        @unless ($event['is_acknowledged'])
                            <form method="POST" action="{{ $event['acknowledge_url'] }}">
                                @csrf
                                <button class="device-text-button" type="submit">
                                    <x-ui-icon name="circle-check" size="sm" />
                                    <span>{{ __('ui.mark_checked_e5aaa31d48') }}</span>
                                </button>
                            </form>
                        @endunless
                        @if ($event['can_add_care'])
                            <form method="POST" action="{{ $event['care_entry_url'] }}">
                                @csrf
                                <input type="hidden" name="confirmed" value="1">
                                <button class="device-text-button" type="submit">
                                    <x-ui-icon name="notebook-pen" size="sm" />
                                    <span>{{ __('ui.add_to_care_journal_363d373726') }}</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @endunless
            </div>
        </article>
    @empty
        <div class="device-empty">
            <x-ui-icon name="shield-check" size="xl" />
            <div>
                <h3>{{ __('ui.no_events_in_this_scope_8196b3adde') }}</h3>
                <p>{{ __('ui.routine_telemetry_remains_separate_from_alerts_that_need_5685e619eb') }}</p>
            </div>
        </div>
    @endforelse
</div>
