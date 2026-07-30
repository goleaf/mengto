@props(['events', 'device' => null, 'shared' => false])

<div class="device-event-list">
    @forelse ($events as $event)
        <article class="device-event device-event--{{ $event['severity'] }}">
            <div class="device-event__icon">
                <x-dynamic-component
                    :component="$event['severity'] === 'routine' ? 'lucide-info' : 'lucide-triangle-alert'"
                    class="size-5"
                    aria-hidden="true"
                />
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
                </div>
                @unless ($shared)
                    <div class="device-event__actions">
                        @unless ($event['is_acknowledged'])
                            <form method="POST" action="{{ $event['acknowledge_url'] }}">
                                @csrf
                                <button class="device-text-button" type="submit">Mark checked</button>
                            </form>
                        @endunless
                        @if ($event['can_add_care'])
                            <form method="POST" action="{{ $event['care_entry_url'] }}">
                                @csrf
                                <input type="hidden" name="confirmed" value="1">
                                <button class="device-text-button" type="submit">Add to care journal</button>
                            </form>
                        @endif
                    </div>
                @endunless
            </div>
        </article>
    @empty
        <div class="device-empty">
            <x-lucide-shield-check class="size-7" aria-hidden="true" />
            <div>
                <h3>No events in this scope</h3>
                <p>Routine telemetry remains separate from alerts that need a person to check them.</p>
            </div>
        </div>
    @endforelse
</div>
