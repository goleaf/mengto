<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="device-directory-header">
            <div>
                <div class="flex items-center gap-2 text-sm font-bold text-paw-leaf">
                    <x-lucide-lock-keyhole class="size-4" aria-hidden="true" />
                    <span>{{ __('ui.private_device_center_a5f6e6b34b') }}</span>
                </div>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('ui.smart_devices_228fd3f770') }}</h1>
                <p class="mt-2 max-w-3xl text-paw-muted">
                    {{ __('ui.location_feeding_water_rest_home_conditions_and_device_837fed39ce') }}
                </p>
            </div>
            <x-action-control :href="route('devices.create')" label="{{ __('ui.connect_device_25367e4d86') }}" icon="plus" variant="primary" size="regular" />
        </header>

        <section class="device-summary" aria-label="{{ __('ui.device_overview_c51d73ad18') }}">
            <div><x-lucide-radio class="size-5" aria-hidden="true" /><span>{{ __('ui.devices_4ba5121d4d') }}</span><strong>{{ $summary['total'] }}</strong></div>
            <div><x-lucide-wifi class="size-5" aria-hidden="true" /><span>{{ __('ui.online_0d21bd5202') }}</span><strong>{{ $summary['online'] }}</strong></div>
            <div><x-lucide-triangle-alert class="size-5" aria-hidden="true" /><span>{{ __('ui.need_attention_f4bcfa7879') }}</span><strong>{{ $summary['needs_attention'] }}</strong></div>
            <div><x-lucide-battery-low class="size-5" aria-hidden="true" /><span>{{ __('ui.low_battery_a894de3c08') }}</span><strong>{{ $summary['low_battery'] }}</strong></div>
        </section>

        <section class="device-privacy-strip">
            <x-lucide-shield-check class="size-5" aria-hidden="true" />
            <div>
                <strong>{{ __('ui.signals_are_evidence_not_certainty_48e95b279a') }}</strong>
                <p>{{ __('ui.shared_devices_never_invent_which_pet_ate_drank_f64fb9ab2b') }}</p>
            </div>
        </section>

        <section class="device-directory-grid" aria-label="{{ __('ui.connected_smart_devices_ab3b109361') }}">
            @forelse ($devices as $device)
                <x-device-card :device="$device" />
            @empty
                <div class="device-empty device-empty--wide">
                    <x-lucide-radio-tower class="size-8" aria-hidden="true" />
                    <div>
                        <h2>{{ __('ui.no_devices_connected_a935aca33c') }}</h2>
                        <p>{{ __('ui.add_a_supported_device_or_keep_an_unsupported_fee199afb1') }}</p>
                    </div>
                    <x-action-control :href="route('devices.create')" label="{{ __('ui.connect_first_device_b30da5e55c') }}" icon="plus" variant="primary" />
                </div>
            @endforelse
        </section>

        @if ($devices->hasPages())
            <div>{{ $devices->links() }}</div>
        @endif
    </div>
</x-app-shell>
