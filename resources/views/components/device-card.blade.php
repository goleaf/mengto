@props(['device'])

<article class="device-card">
    <a href="{{ $device['show_url'] }}" class="device-card__icon" aria-label="{{ __('presentation.open_device', ['name' => $device['name']]) }}">
        <x-ui-icon size="xl" :name="$device['icon']" />
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

        <div class="device-card__pets" aria-label="{{ __('ui.assigned_pets_dd50d74ca4') }}">
            <x-ui-icon name="paw-print" size="sm" />
            <span>{{ $device['pets'] === [] ? __('ui.shared_zone_pet_not_identified_0d7d91e867') : implode(' · ', $device['pets']) }}</span>
        </div>

        <dl class="device-card__metrics">
            <div>
                <dt>{{ __('ui.connection_639a40e82b') }}</dt>
                <dd><span class="device-dot device-dot--{{ $device['connection_tone'] }}"></span>{{ $device['connection_label'] }}</dd>
            </div>
            <div>
                <dt>{{ __('ui.battery_dfcb7c1619') }}</dt>
                <dd>{{ $device['battery_label'] }}</dd>
            </div>
            <div>
                <dt>{{ __('ui.last_signal_6f2cfbf3ce') }}</dt>
                <dd>{{ $device['last_seen'] }}</dd>
            </div>
            <div>
                <dt>{{ __('ui.area_024dc204d7') }}</dt>
                <dd>{{ $device['public_zone_label'] }}</dd>
            </div>
        </dl>

        @if ($device['open_events_count'] > 0)
            <div class="device-card__alert">
                <x-ui-icon name="triangle-alert" size="sm" />
                <span>{{ $device['event_summary'] }}</span>
            </div>
        @endif
    </div>
</article>
