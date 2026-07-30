@props(['device'])

<article class="device-card">
    <a href="{{ $device['show_url'] }}" class="device-card__icon" aria-label="Open {{ $device['name'] }}">
        <x-dynamic-component :component="'lucide-'.$device['icon']" class="size-7" aria-hidden="true" />
    </a>
    <div class="device-card__body">
        <div class="device-card__heading">
            <div>
                <p>{{ $device['type_label'] }}</p>
                <h2><a href="{{ $device['show_url'] }}">{{ $device['name'] }}</a></h2>
            </div>
            <x-status-badge
                :label="$device['status_label']"
                :icon="$device['status'] === 'needs-attention' ? 'circle-alert' : 'shield-check'"
                :tone="$device['status_tone']"
            />
        </div>

        <div class="device-card__pets" aria-label="Assigned pets">
            <x-lucide-paw-print class="size-4" aria-hidden="true" />
            <span>{{ $device['pets'] === [] ? 'Shared zone, pet not identified' : implode(' · ', $device['pets']) }}</span>
        </div>

        <dl class="device-card__metrics">
            <div>
                <dt>Connection</dt>
                <dd><span class="device-dot device-dot--{{ $device['connection_tone'] }}"></span>{{ $device['connection_label'] }}</dd>
            </div>
            <div>
                <dt>Battery</dt>
                <dd>{{ $device['battery_label'] }}</dd>
            </div>
            <div>
                <dt>Last signal</dt>
                <dd>{{ $device['last_seen'] }}</dd>
            </div>
            <div>
                <dt>Area</dt>
                <dd>{{ $device['public_zone_label'] }}</dd>
            </div>
        </dl>

        @if ($device['open_events_count'] > 0)
            <div class="device-card__alert">
                <x-lucide-triangle-alert class="size-4" aria-hidden="true" />
                <span>
                    {{ $device['open_events_count'] }} open
                    {{ str('event')->plural($device['open_events_count']) }}
                    @if ($device['urgent_events_count'] > 0)
                        · {{ $device['urgent_events_count'] }} urgent
                    @endif
                </span>
            </div>
        @endif
    </div>
</article>
