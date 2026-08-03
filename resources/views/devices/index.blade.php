<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <x-page-header
            :eyebrow="__('ui.private_device_center_a5f6e6b34b')"
            :title="__('ui.smart_devices_228fd3f770')"
            :description="__('ui.location_feeding_water_rest_home_conditions_and_device_837fed39ce')"
            heading-id="devices-heading"
            :action-label="__('ui.connect_device_25367e4d86')"
            action-icon="plus"
            :action-href="route('devices.create')"
            data-section="devices-header"
        />

        <section class="device-summary" aria-label="{{ __('ui.device_overview_c51d73ad18') }}">
            <div><x-ui-icon name="radio" size="lg" /><span>{{ __('ui.devices_4ba5121d4d') }}</span><strong>{{ $summary['total'] }}</strong></div>
            <div><x-ui-icon name="wifi" size="lg" /><span>{{ __('ui.online_0d21bd5202') }}</span><strong>{{ $summary['online'] }}</strong></div>
            <div><x-ui-icon name="triangle-alert" size="lg" /><span>{{ __('ui.need_attention_f4bcfa7879') }}</span><strong>{{ $summary['needs_attention'] }}</strong></div>
            <div><x-ui-icon name="battery-low" size="lg" /><span>{{ __('ui.low_battery_a894de3c08') }}</span><strong>{{ $summary['low_battery'] }}</strong></div>
        </section>

        <section class="device-privacy-strip">
            <x-ui-icon name="shield-check" size="lg" />
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
                    <x-ui-icon name="radio-tower" size="2xl" />
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
