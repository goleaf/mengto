<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-4xl gap-7">
        <x-page-header
            :eyebrow="__('ui.ownership_first')"
            :title="__('ui.connect_a_device')"
            :description="__('ui.create_a_private_inventory_record_assign_the_correct_pets_and_confirm_how_the_device_works_before_any_signal_is_trusted')"
            heading-id="connect-device-heading"
            :action-label="__('ui.smart_devices')"
            action-icon="arrow-left"
            :action-href="route('devices.index')"
            action-variant="paper"
            data-section="device-create-header"
        />

        @if ($errors->any())
            <div class="device-form-errors" role="alert">
                <x-ui-icon name="circle-alert" size="lg" />
                <div>
                    <strong>{{ __('ui.the_device_was_not_connected') }}</strong>
                    <ul>
                        @forelse ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @empty
                            <li>{{ __('ui.validation_failed') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('devices.store') }}" class="device-form-section">
            @csrf
            <div class="device-form-grid">
                <label>
                    {{ __('ui.device_name') }}
                    <input name="name" value="{{ old('name') }}" maxlength="120" required placeholder="{{ __('ui.scout_gps') }}">
                </label>
                <label>
                    {{ __('ui.device_type') }}
                    <select name="type" required>
                        <option value="">{{ __('ui.choose_a_type') }}</option>
                        @forelse ($device_types as $type)
                            <option value="{{ $type['value'] }}" @selected(old('type') === $type['value'])>{{ $type['label'] }}</option>
                        @empty
                            <option value="" disabled>{{ __('ui.no_supported_types') }}</option>
                        @endforelse
                    </select>
                </label>
                <label>
                    {{ __('ui.brand_label') }}
                    <input name="brand" value="{{ old('brand') }}" maxlength="100">
                </label>
                <label>
                    {{ __('ui.model') }}
                    <input name="model" value="{{ old('model') }}" maxlength="120">
                </label>
                <label>
                    {{ __('ui.serial_number') }}
                    <input name="serial_number" value="{{ old('serial_number') }}" maxlength="255" autocomplete="off">
                    <small>{{ __('ui.encrypted_and_masked_outside_owner_settings') }}</small>
                </label>
                <label>
                    {{ __('ui.connection') }}
                    <select name="connection_type">
                        <option value="">{{ __('ui.not_recorded') }}</option>
                        @forelse ($connection_types as $connection)
                            <option value="{{ $connection['value'] }}" @selected(old('connection_type') === $connection['value'])>{{ $connection['label'] }}</option>
                        @empty
                            <option value="">{{ __('ui.no_connections') }}</option>
                        @endforelse
                    </select>
                </label>
                <label>
                    {{ __('ui.public_area_label') }}
                    <input name="public_zone_label" value="{{ old('public_zone_label') }}" maxlength="160" placeholder="{{ __('ui.home_area') }}">
                </label>
                <label>
                    {{ __('ui.exact_installation_place') }}
                    <input name="private_location_label" value="{{ old('private_location_label') }}" maxlength="500" placeholder="{{ __('ui.hallway_shelf') }}">
                </label>
                <label>
                    {{ __('ui.firmware') }}
                    <input name="firmware_version" value="{{ old('firmware_version') }}" maxlength="80">
                </label>
                <label>
                    {{ __('ui.battery_percent') }}
                    <input type="number" name="battery_percent" value="{{ old('battery_percent') }}" min="0" max="100">
                </label>
            </div>

            <fieldset>
                <legend>{{ __('ui.assigned_pets') }}</legend>
                <div class="device-check-grid">
                    @forelse ($pets as $pet)
                        <label class="device-check">
                            <input type="checkbox" name="pet_profile_keys[]" value="{{ $pet['key'] }}" @checked(in_array($pet['key'], old('pet_profile_keys', []), true))>
                            <span><strong>{{ $pet['name'] }}</strong><small>{{ $pet['species'] }}</small></span>
                        </label>
                    @empty
                        <span>{{ __('ui.no_managed_pets_sentence') }}</span>
                    @endforelse
                </div>
            </fieldset>

            <div class="device-check-grid">
                <label class="device-check"><input type="checkbox" name="has_backup_power" value="1"><span>{{ __('ui.backup_power') }}</span></label>
                <label class="device-check"><input type="checkbox" name="supports_local_operation" value="1"><span>{{ __('ui.works_locally_offline') }}</span></label>
                <label class="device-check"><input type="checkbox" name="requires_cloud" value="1"><span>{{ __('ui.requires_manufacturer_cloud') }}</span></label>
                <label class="device-check"><input type="checkbox" name="is_medical_device" value="1"><span>{{ __('ui.professional_or_medical_device') }}</span></label>
            </div>

            <label class="device-check device-check--boxed">
                <input type="checkbox" name="ownership_confirmed" value="1" required>
                <span>{{ __('ui.i_own_or_am_authorized_to_connect_this_device_and_it_is_not_still_bound_to_another_account') }}</span>
            </label>
            <label class="device-check device-check--boxed">
                <input type="checkbox" name="privacy_acknowledged" value="1" required>
                <span>{{ __('ui.i_understand_that_location_home_routines_camera_data_and_device_payloads_are_private_by_default') }}</span>
            </label>

            <button type="submit" class="action action--primary">
                <x-ui-icon name="link" />
                <span>{{ __('ui.connect_privately') }}</span>
            </button>
        </form>
    </div>
</x-app-shell>
