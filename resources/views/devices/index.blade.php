<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="device-directory-header">
            <div>
                <div class="flex items-center gap-2 text-sm font-bold text-paw-leaf">
                    <x-lucide-lock-keyhole class="size-4" aria-hidden="true" />
                    <span>Private device center</span>
                </div>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Smart devices</h1>
                <p class="mt-2 max-w-3xl text-paw-muted">
                    Location, feeding, water, rest, home conditions, and device safety with the source and confidence kept visible.
                </p>
            </div>
            <x-action-control :href="route('devices.create')" label="Connect device" icon="plus" variant="primary" size="regular" />
        </header>

        <section class="device-summary" aria-label="Device overview">
            <div><x-lucide-radio class="size-5" aria-hidden="true" /><span>Devices</span><strong>{{ $summary['total'] }}</strong></div>
            <div><x-lucide-wifi class="size-5" aria-hidden="true" /><span>Online</span><strong>{{ $summary['online'] }}</strong></div>
            <div><x-lucide-triangle-alert class="size-5" aria-hidden="true" /><span>Need attention</span><strong>{{ $summary['needs_attention'] }}</strong></div>
            <div><x-lucide-battery-low class="size-5" aria-hidden="true" /><span>Low battery</span><strong>{{ $summary['low_battery'] }}</strong></div>
        </section>

        <section class="device-privacy-strip">
            <x-lucide-shield-check class="size-5" aria-hidden="true" />
            <div>
                <strong>Signals are evidence, not certainty</strong>
                <p>Shared devices never invent which pet ate, drank, or used a litter box. Home coordinates, camera access, serial numbers, and raw payloads remain private.</p>
            </div>
        </section>

        <section class="device-directory-grid" aria-label="Connected smart devices">
            @forelse ($devices as $device)
                <x-device-card :device="$device" />
            @empty
                <div class="device-empty device-empty--wide">
                    <x-lucide-radio-tower class="size-8" aria-hidden="true" />
                    <div>
                        <h2>No devices connected</h2>
                        <p>Add a supported device or keep an unsupported model as a private manual inventory item.</p>
                    </div>
                    <x-action-control :href="route('devices.create')" label="Connect first device" icon="plus" variant="primary" />
                </div>
            @endforelse
        </section>

        @if ($devices->hasPages())
            <div>{{ $devices->links() }}</div>
        @endif
    </div>
</x-app-shell>
