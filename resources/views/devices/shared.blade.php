<x-app-shell :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-5xl gap-7">
        <header class="device-shared-header">
            <div class="device-detail-header__identity">
                <span><x-ui-icon size="2xl" :name="$device['icon']" /></span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-status-badge label="{{ __('ui.temporary_access') }}" icon="key-round" tone="warning" />
                        <x-status-badge :label="$grant['recipient_role']" icon="user-round" tone="surface" />
                    </div>
                    <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $device['name'] }}</h1>
                    <p class="mt-2 text-paw-muted">{{ __('presentation.access_expires_views', ['label' => $grant['label'], 'expires' => $grant['expires_at'], 'views' => $grant['views']]) }}</p>
                </div>
            </div>
        </header>

        <section class="device-access-scope">
            <x-ui-icon name="shield-check" size="lg" />
            <div>
                <strong>{{ __('ui.only_explicitly_granted_device_data_is_visible') }}</strong>
                <p>{{ __('ui.exact_home_coordinates_serial_number_raw_payloads_household_camera_history_and_unrelated_pet_records_are_not_included') }}</p>
            </div>
        </section>

        <section class="device-status-strip" aria-label="{{ __('ui.shared_device_status') }}">
            <div><span class="device-status-strip__icon"><x-ui-icon name="radio" size="lg" /></span><small>{{ __('ui.status') }}</small><strong>{{ $device['status_label'] }}</strong></div>
            <div><span class="device-status-strip__icon"><x-ui-icon name="wifi" size="lg" /></span><small>{{ __('ui.connection') }}</small><strong>{{ $device['connection_label'] }}</strong></div>
            <div><span class="device-status-strip__icon"><x-ui-icon name="battery-medium" size="lg" /></span><small>{{ __('ui.battery') }}</small><strong>{{ $device['battery_label'] }}</strong></div>
            <div><span class="device-status-strip__icon"><x-ui-icon name="map-pin" size="lg" /></span><small>{{ __('ui.shared_area') }}</small><strong>{{ $device['location_label'] }}</strong></div>
        </section>

        <section class="device-panel">
            <div class="device-panel__heading"><div><p>{{ __('ui.shared_alerts') }}</p><h2>{{ __('ui.recent_events') }}</h2></div></div>
            <x-device-event-list :events="$events" :shared="true" />
        </section>

        <section class="device-panel">
            <div class="device-panel__heading"><div><p>{{ __('ui.source_and_confidence_visible') }}</p><h2>{{ __('ui.recent_readings') }}</h2></div></div>
            <x-device-reading-table :readings="$readings" :shared="true" />
        </section>
    </div>
</x-app-shell>
