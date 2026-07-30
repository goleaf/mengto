<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-5xl gap-7">
        <header class="device-shared-header">
            <div class="device-detail-header__identity">
                <span><x-dynamic-component :component="'lucide-'.$device['icon']" class="size-8" aria-hidden="true" /></span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-status-badge label="Temporary access" icon="key-round" tone="warning" />
                        <x-status-badge :label="$grant['recipient_role']" icon="user-round" tone="surface" />
                    </div>
                    <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $device['name'] }}</h1>
                    <p class="mt-2 text-paw-muted">{{ $grant['label'] }} · expires {{ $grant['expires_at'] }} · view {{ $grant['views'] }}</p>
                </div>
            </div>
        </header>

        <section class="device-access-scope">
            <x-lucide-shield-check class="size-5" aria-hidden="true" />
            <div>
                <strong>Only explicitly granted device data is visible</strong>
                <p>Exact home coordinates, serial number, raw payloads, household camera history, and unrelated pet records are not included.</p>
            </div>
        </section>

        <section class="device-status-strip" aria-label="Shared device status">
            <div><span class="device-status-strip__icon"><x-lucide-radio class="size-5" aria-hidden="true" /></span><small>Status</small><strong>{{ $device['status_label'] }}</strong></div>
            <div><span class="device-status-strip__icon"><x-lucide-wifi class="size-5" aria-hidden="true" /></span><small>Connection</small><strong>{{ $device['connection_label'] }}</strong></div>
            <div><span class="device-status-strip__icon"><x-lucide-battery-medium class="size-5" aria-hidden="true" /></span><small>Battery</small><strong>{{ $device['battery_label'] }}</strong></div>
            <div><span class="device-status-strip__icon"><x-lucide-map-pin class="size-5" aria-hidden="true" /></span><small>Shared area</small><strong>{{ $device['location_label'] }}</strong></div>
        </section>

        <section class="device-panel">
            <div class="device-panel__heading"><div><p>Shared alerts</p><h2>Recent events</h2></div></div>
            <x-device-event-list :events="$events" :shared="true" />
        </section>

        <section class="device-panel">
            <div class="device-panel__heading"><div><p>Source and confidence visible</p><h2>Recent readings</h2></div></div>
            <x-device-reading-table :readings="$readings" :shared="true" />
        </section>
    </div>
</x-app-shell>
