<x-app-shell :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <x-page-header
            :eyebrow="__('ui.private_device_center')"
            :title="__('ui.smart_devices')"
            :description="__('ui.location_feeding_water_rest_home_conditions_and_device_safety_with_the_source_and_confidence_kept_visible')"
            heading-id="devices-heading"
            :action-label="__('ui.connect_device')"
            action-icon="plus"
            :action-href="route('devices.create')"
            data-section="devices-header"
        />

        <section class="device-summary" aria-label="{{ __('ui.device_overview') }}">
            <div><x-ui-icon name="radio" size="lg" /><span>{{ __('ui.devices') }}</span><strong>{{ $summary['total'] }}</strong></div>
            <div><x-ui-icon name="wifi" size="lg" /><span>{{ __('ui.online') }}</span><strong>{{ $summary['online'] }}</strong></div>
            <div><x-ui-icon name="triangle-alert" size="lg" /><span>{{ __('ui.need_attention') }}</span><strong>{{ $summary['needs_attention'] }}</strong></div>
            <div><x-ui-icon name="battery-low" size="lg" /><span>{{ __('ui.low_battery') }}</span><strong>{{ $summary['low_battery'] }}</strong></div>
        </section>

        <section class="device-privacy-strip">
            <x-ui-icon name="shield-check" size="lg" />
            <div>
                <strong>{{ __('ui.signals_are_evidence_not_certainty') }}</strong>
                <p>{{ __('ui.shared_devices_never_invent_which_pet_ate_drank_or_used_a_litter_box_home_coordinates_camera_access_serial_numbers_and_raw_payloads_remain_private') }}</p>
            </div>
        </section>

        <section class="device-directory-grid" aria-label="{{ __('ui.connected_smart_devices') }}">
            @forelse ($devices as $device)
                <x-device-card :device="$device" />
            @empty
                <div class="device-empty device-empty--wide">
                    <x-ui-icon name="radio-tower" size="2xl" />
                    <div>
                        <h2>{{ __('ui.no_devices_connected') }}</h2>
                        <p>{{ __('ui.add_a_supported_device_or_keep_an_unsupported_model_as_a_private_manual_inventory_item') }}</p>
                    </div>
                    <x-action-control :href="route('devices.create')" label="{{ __('ui.connect_first_device') }}" icon="plus" variant="primary" />
                </div>
            @endforelse
        </section>

        @if ($devices->hasPages())
            <div>{{ $devices->links() }}</div>
        @endif
    </div>
</x-app-shell>
