<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-4xl gap-7">
        <x-page-header
            :eyebrow="__('ui.ownership_first_9c51bc8cc9')"
            :title="__('ui.connect_a_device_0b4a51f394')"
            :description="__('ui.create_a_private_inventory_record_assign_the_correct_aa8293bef0')"
            heading-id="connect-device-heading"
            :action-label="__('ui.smart_devices_228fd3f770')"
            action-icon="arrow-left"
            :action-href="route('devices.index')"
            action-variant="paper"
            data-section="device-create-header"
        />

        @if ($errors->any())
            <div class="device-form-errors" role="alert">
                <x-ui-icon name="circle-alert" size="lg" />
                <div>
                    <strong>{{ __('ui.the_device_was_not_connected_2169b1d288') }}</strong>
                    <ul>
                        @forelse ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @empty
                            <li>{{ __('ui.validation_failed_fa0dce7e0b') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('devices.store') }}" class="device-form-section">
            @csrf
            <div class="device-form-grid">
                <label>
                    {{ __('ui.device_name_155106be11') }}
                    <input name="name" value="{{ old('name') }}" maxlength="120" required placeholder="{{ __('ui.scout_gps_0fb7d9221f') }}">
                </label>
                <label>
                    {{ __('ui.device_type_8562a30fdd') }}
                    <select name="type" required>
                        <option value="">{{ __('ui.choose_a_type_251f71e358') }}</option>
                        @forelse ($device_types as $type)
                            <option value="{{ $type['value'] }}" @selected(old('type') === $type['value'])>{{ $type['label'] }}</option>
                        @empty
                            <option value="" disabled>{{ __('ui.no_supported_types_7098fb9cad') }}</option>
                        @endforelse
                    </select>
                </label>
                <label>
                    {{ __('ui.brand_090ed4316f') }}
                    <input name="brand" value="{{ old('brand') }}" maxlength="100">
                </label>
                <label>
                    {{ __('ui.model_5e2c614c23') }}
                    <input name="model" value="{{ old('model') }}" maxlength="120">
                </label>
                <label>
                    {{ __('ui.serial_number_f2307baf1c') }}
                    <input name="serial_number" value="{{ old('serial_number') }}" maxlength="255" autocomplete="off">
                    <small>{{ __('ui.encrypted_and_masked_outside_owner_settings_1f82f19d42') }}</small>
                </label>
                <label>
                    {{ __('ui.connection_639a40e82b') }}
                    <select name="connection_type">
                        <option value="">{{ __('ui.not_recorded_b37c7879f6') }}</option>
                        @forelse ($connection_types as $connection)
                            <option value="{{ $connection['value'] }}" @selected(old('connection_type') === $connection['value'])>{{ $connection['label'] }}</option>
                        @empty
                            <option value="">{{ __('ui.no_connections_9fc195c94c') }}</option>
                        @endforelse
                    </select>
                </label>
                <label>
                    {{ __('ui.public_area_label_5d9b790916') }}
                    <input name="public_zone_label" value="{{ old('public_zone_label') }}" maxlength="160" placeholder="{{ __('ui.home_area_df8f366499') }}">
                </label>
                <label>
                    {{ __('ui.exact_installation_place_80a9e88189') }}
                    <input name="private_location_label" value="{{ old('private_location_label') }}" maxlength="500" placeholder="{{ __('ui.hallway_shelf_fe5445c71d') }}">
                </label>
                <label>
                    {{ __('ui.firmware_c2a314c3b3') }}
                    <input name="firmware_version" value="{{ old('firmware_version') }}" maxlength="80">
                </label>
                <label>
                    {{ __('ui.battery_percent_2fa5d7a972') }}
                    <input type="number" name="battery_percent" value="{{ old('battery_percent') }}" min="0" max="100">
                </label>
            </div>

            <fieldset>
                <legend>{{ __('ui.assigned_pets_dd50d74ca4') }}</legend>
                <div class="device-check-grid">
                    @forelse ($pets as $pet)
                        <label class="device-check">
                            <input type="checkbox" name="pet_profile_keys[]" value="{{ $pet['key'] }}" @checked(in_array($pet['key'], old('pet_profile_keys', []), true))>
                            <span><strong>{{ $pet['name'] }}</strong><small>{{ $pet['species'] }}</small></span>
                        </label>
                    @empty
                        <span>{{ __('ui.no_managed_pets_3c6570a574') }}</span>
                    @endforelse
                </div>
            </fieldset>

            <div class="device-check-grid">
                <label class="device-check"><input type="checkbox" name="has_backup_power" value="1"><span>{{ __('ui.backup_power_c459b63fee') }}</span></label>
                <label class="device-check"><input type="checkbox" name="supports_local_operation" value="1"><span>{{ __('ui.works_locally_offline_dffec6f4f7') }}</span></label>
                <label class="device-check"><input type="checkbox" name="requires_cloud" value="1"><span>{{ __('ui.requires_manufacturer_cloud_a254c149e3') }}</span></label>
                <label class="device-check"><input type="checkbox" name="is_medical_device" value="1"><span>{{ __('ui.professional_or_medical_device_8059af0cfc') }}</span></label>
            </div>

            <label class="device-check device-check--boxed">
                <input type="checkbox" name="ownership_confirmed" value="1" required>
                <span>{{ __('ui.i_own_or_am_authorized_to_connect_this_83251e5f9b') }}</span>
            </label>
            <label class="device-check device-check--boxed">
                <input type="checkbox" name="privacy_acknowledged" value="1" required>
                <span>{{ __('ui.i_understand_that_location_home_routines_camera_data_38cf218ee0') }}</span>
            </label>

            <button type="submit" class="action action--primary">
                <x-ui-icon name="link" />
                <span>{{ __('ui.connect_privately_363eb4f684') }}</span>
            </button>
        </form>
    </div>
</x-app-shell>
