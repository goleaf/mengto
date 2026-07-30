<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-4xl gap-7">
        <header class="border-b border-paw-line pb-6">
            <a href="{{ route('devices.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                Smart devices
            </a>
            <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">Ownership first</p>
            <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Connect a device</h1>
            <p class="mt-2 max-w-2xl text-paw-muted">Create a private inventory record, assign the correct pets, and confirm how the device works before any signal is trusted.</p>
        </header>

        @if ($errors->any())
            <div class="device-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>The device was not connected</strong>
                    <ul>
                        @forelse ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @empty
                            <li>Validation failed.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('devices.store') }}" class="device-form-section">
            @csrf
            <div class="device-form-grid">
                <label>
                    Device name
                    <input name="name" value="{{ old('name') }}" maxlength="120" required placeholder="Scout GPS">
                </label>
                <label>
                    Device type
                    <select name="type" required>
                        <option value="">Choose a type</option>
                        @forelse ($device_types as $type)
                            <option value="{{ $type['value'] }}" @selected(old('type') === $type['value'])>{{ $type['label'] }}</option>
                        @empty
                            <option value="" disabled>No supported types</option>
                        @endforelse
                    </select>
                </label>
                <label>
                    Brand
                    <input name="brand" value="{{ old('brand') }}" maxlength="100">
                </label>
                <label>
                    Model
                    <input name="model" value="{{ old('model') }}" maxlength="120">
                </label>
                <label>
                    Serial number
                    <input name="serial_number" value="{{ old('serial_number') }}" maxlength="255" autocomplete="off">
                    <small>Encrypted and masked outside owner settings.</small>
                </label>
                <label>
                    Connection
                    <select name="connection_type">
                        <option value="">Not recorded</option>
                        @forelse (['wi-fi', 'bluetooth', 'cellular', 'radio', 'matter', 'manual'] as $connection)
                            <option value="{{ $connection }}" @selected(old('connection_type') === $connection)>{{ str($connection)->headline() }}</option>
                        @empty
                            <option value="">No connections</option>
                        @endforelse
                    </select>
                </label>
                <label>
                    Public area label
                    <input name="public_zone_label" value="{{ old('public_zone_label') }}" maxlength="160" placeholder="Home area">
                </label>
                <label>
                    Exact installation place
                    <input name="private_location_label" value="{{ old('private_location_label') }}" maxlength="500" placeholder="Hallway shelf">
                </label>
                <label>
                    Firmware
                    <input name="firmware_version" value="{{ old('firmware_version') }}" maxlength="80">
                </label>
                <label>
                    Battery percent
                    <input type="number" name="battery_percent" value="{{ old('battery_percent') }}" min="0" max="100">
                </label>
            </div>

            <fieldset>
                <legend>Assigned pets</legend>
                <div class="device-check-grid">
                    @forelse ($pets as $pet)
                        <label class="device-check">
                            <input type="checkbox" name="pet_profile_keys[]" value="{{ $pet['key'] }}" @checked(in_array($pet['key'], old('pet_profile_keys', []), true))>
                            <span><strong>{{ $pet['name'] }}</strong><small>{{ $pet['species'] }}</small></span>
                        </label>
                    @empty
                        <span>No managed pets.</span>
                    @endforelse
                </div>
            </fieldset>

            <div class="device-check-grid">
                <label class="device-check"><input type="checkbox" name="has_backup_power" value="1"><span>Backup power</span></label>
                <label class="device-check"><input type="checkbox" name="supports_local_operation" value="1"><span>Works locally offline</span></label>
                <label class="device-check"><input type="checkbox" name="requires_cloud" value="1"><span>Requires manufacturer cloud</span></label>
                <label class="device-check"><input type="checkbox" name="is_medical_device" value="1"><span>Professional or medical device</span></label>
            </div>

            <label class="device-check device-check--boxed">
                <input type="checkbox" name="ownership_confirmed" value="1" required>
                <span>I own or am authorized to connect this device, and it is not still bound to another account.</span>
            </label>
            <label class="device-check device-check--boxed">
                <input type="checkbox" name="privacy_acknowledged" value="1" required>
                <span>I understand that location, home routines, camera data, and device payloads are private by default.</span>
            </label>

            <button type="submit" class="action action--primary">
                <x-lucide-link class="icon" aria-hidden="true" />
                <span>Connect privately</span>
            </button>
        </form>
    </div>
</x-app-shell>
