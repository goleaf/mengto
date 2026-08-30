<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-6">
        <x-page-header
            :eyebrow="__('ui.urgent_report')"
            :title="__('ui.report_a_missing_or_found_animal')"
            :description="__('ui.publish_the_essential_location_and_identification_details_now_exact_coordinates_and_direct_contact_details_stay_protected')"
            heading-id="create-lost-found-report-heading"
            :action-label="__('ui.lost_found')"
            action-icon="arrow-left"
            :action-href="route('lost-found.index')"
            action-variant="paper"
            data-section="lost-found-create-header"
        />

        @if ($errors->any())
            <div class="rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-900" role="alert">
                <div class="flex items-center gap-2 font-bold">
                    <x-ui-icon name="circle-alert" size="lg" />
                    {{ __('ui.check_the_highlighted_fields') }}
                </div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @forelse ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @empty
                        <li>{{ __('ui.no_validation_details_are_available') }}</li>
                    @endforelse
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('lost-found.store') }}" enctype="multipart/form-data" class="lost-found-case-form grid gap-8">
            @csrf
            <input type="hidden" name="intent" value="publish">
            <input type="hidden" name="country" value="{{ $default_country }}">

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="report-kind-title">
                <h2 id="report-kind-title" class="text-xl font-bold">{{ __('ui.what_happened_question') }}</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    @forelse ($types as $value => $label)
                        <label class="flex cursor-pointer items-start gap-3 rounded-md border border-paw-line bg-white p-4 has-[:checked]:border-paw-leaf has-[:checked]:bg-paw-mint">
                            <input type="radio" name="type" value="{{ $value }}" class="mt-1" @checked(old('type', $default_type) === $value)>
                            <span>
                                <strong class="block">{{ $label }}</strong>
                                <span class="mt-1 block text-sm text-paw-muted">
                                    {{ $type_descriptions[$value] }}
                                </span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-paw-muted">{{ __('ui.report_types_are_unavailable') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="animal-title">
                <h2 id="animal-title" class="text-xl font-bold">{{ __('ui.animal_details') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.pet_profile') }}
                        <select name="pet_profile_id" class="min-h-11 rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">{{ __('ui.no_saved_profile') }}</option>
                            @forelse ($pet_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('pet_profile_id', $default_pet_id) == $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_profiles') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.name') }}
                        <input name="pet_name" value="{{ old('pet_name', $default_pet['name'] ?? '') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="100">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.species') }}
                        <select name="species" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                            @forelse ($species_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('species', $default_species) === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_species') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.breed_or_mix') }}
                        <input name="breed" value="{{ old('breed', $default_pet['breed'] ?? '') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="120">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.primary_color') }}
                        <input name="primary_color" value="{{ old('primary_color') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="80">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.size') }}
                        <select name="size" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">{{ __('ui.choose_size') }}</option>
                            @forelse ($size_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('size', $default_size) === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_size_options') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.sex') }}
                        <select name="sex" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">{{ __('ui.unknown') }}</option>
                            <option value="male" @selected(old('sex') === 'male')>{{ __('ui.male') }}</option>
                            <option value="female" @selected(old('sex') === 'female')>{{ __('ui.female') }}</option>
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.age') }}
                        <input name="age_label" value="{{ old('age_label', $default_pet['age'] ?? '') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="80" placeholder="{{ __('ui.4_years') }}">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.microchip') }}
                        <select name="microchip_status" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($microchip_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('microchip_status', $default_microchip_status) === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_microchip_options') }}</option>
                            @endforelse
                        </select>
                    </label>
                </div>

                <livewire:forum.animal-taxonomy-selector
                    :selected="old('taxon_id')"
                    input-name="taxon_id"
                    :selection-limit="1"
                />

                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.public_description') }}
                    <textarea name="description" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="4000">{{ old('description') }}</textarea>
                </label>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.visible_identifying_marks') }}
                        <textarea name="distinctive_marks" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1500">{{ old('distinctive_marks') }}</textarea>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.hidden_verification_mark') }}
                        <textarea name="hidden_marks" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1000">{{ old('hidden_marks') }}</textarea>
                        <span class="text-xs font-normal text-paw-muted">{{ __('ui.visible_only_to_the_search_coordinator') }}</span>
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('lost_found.interface.temperament') }}
                        <input name="temperament" value="{{ old('temperament') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="300">
                        <span class="text-xs font-normal text-paw-muted">{{ __('lost_found.interface.temperament_help') }}</span>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('lost_found.interface.collar_accessories') }}
                        <input name="accessories[]" value="{{ old('accessories.0') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="80">
                        <span class="text-xs font-normal text-paw-muted">{{ __('lost_found.interface.collar_accessories_help') }}</span>
                    </label>
                </div>

                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.current_photos') }}
                    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    <span class="text-xs font-normal text-paw-muted">{{ __('lost_found.interface.photo_limits') }}</span>
                </label>
            </section>

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="location-title">
                <h2 id="location-title" class="text-xl font-bold">{{ __('ui.last_location_and_time') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.public_area') }}
                        <input name="last_seen_area" value="{{ old('last_seen_area') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="160" placeholder="{{ __('ui.vingis_park_western_entrance') }}">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.city') }}
                        <input name="city" value="{{ old('city', __('ui.vilnius')) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="100">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.observed_at') }}
                        <input type="datetime-local" name="last_seen_at" value="{{ old('last_seen_at', $default_last_seen_at) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.direction') }}
                        <input name="direction" value="{{ old('direction') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="100" placeholder="{{ __('ui.toward_the_river_path') }}">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.exact_latitude') }}
                        <input type="number" step="0.000001" name="latitude" value="{{ old('latitude') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.exact_longitude') }}
                        <input type="number" step="0.000001" name="longitude" value="{{ old('longitude') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2">
                        {{ __('ui.exact_location_note') }}
                        <input name="location_note" value="{{ old('location_note') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="300" placeholder="{{ __('ui.bench_beside_the_west_gate') }}">
                        <span class="text-xs font-normal text-paw-muted">{{ __('ui.encrypted_and_limited_to_the_search_team_public_coordinates_are_generalized') }}</span>
                    </label>
                </div>
            </section>

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="safety-title">
                <h2 id="safety-title" class="text-xl font-bold">{{ __('ui.safe_approach') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.what_to_do') }}
                        <textarea name="approach_instructions" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1500">{{ old('approach_instructions') }}</textarea>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.what_to_avoid') }}
                        <textarea name="avoid_instructions" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1500">{{ old('avoid_instructions') }}</textarea>
                    </label>
                </div>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.health_or_immediate_safety_notice') }}
                    <input name="health_notice" value="{{ old('health_notice') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1000" placeholder="{{ __('ui.needs_regular_medication_contact_the_owner_quickly') }}">
                </label>
            </section>

            <section class="grid gap-4" aria-labelledby="contact-title">
                <h2 id="contact-title" class="text-xl font-bold">{{ __('ui.protected_contact_and_alerts') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.contact_channel') }}
                        <select name="contact_channel" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="platform" @selected(old('contact_channel', 'platform') === 'platform')>{{ __('ui.protected_platform_messages') }}</option>
                            <option value="email" @selected(old('contact_channel') === 'email')>{{ __('ui.protected_email') }}</option>
                            <option value="phone" @selected(old('contact_channel') === 'phone')>{{ __('ui.protected_phone') }}</option>
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.email_or_phone_when_selected') }}
                        <input name="contact_value" value="{{ old('contact_value') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="160">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.alert_radius') }}
                        <select name="notification_radius_km" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($notification_radius_options as $radius => $label)
                                <option value="{{ $radius }}" @selected(old('notification_radius_km', $default_notification_radius) == $radius)>{{ $label }}</option>
                            @empty
                                <option value="">{{ __('ui.no_radius_options_available') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.visibility') }}
                        <select name="visibility" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="public" @selected(old('visibility', 'public') === 'public')>{{ __('ui.public_directory') }}</option>
                            <option value="link" @selected(old('visibility') === 'link')>{{ __('ui.accessible_by_link') }}</option>
                            <option value="registered" @selected(old('visibility') === 'registered')>{{ __('ui.registered_community') }}</option>
                            <option value="local-group" @selected(old('visibility') === 'local-group')>{{ __('ui.local_group_only') }}</option>
                        </select>
                    </label>
                </div>

                <div class="grid gap-3 rounded-md border border-paw-line bg-white p-4">
                    <label class="flex min-h-11 items-start gap-3">
                        <input type="checkbox" name="reward_offered" value="1" class="mt-1" @checked(old('reward_offered'))>
                        <span class="text-sm">
                            <strong class="block">{{ __('lost_found.interface.reward_offered') }}</strong>
                            <span class="mt-1 block text-paw-muted">{{ __('lost_found.interface.reward_safety_help') }}</span>
                        </span>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('lost_found.interface.reward_summary') }}
                        <textarea name="reward_summary" rows="2" maxlength="300" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('reward_summary') }}</textarea>
                    </label>
                </div>

                <label class="flex items-start gap-3 rounded-md border border-paw-line bg-white p-4">
                    <input type="checkbox" name="animal_secured" value="1" class="mt-1" @checked(old('animal_secured'))>
                    <span class="text-sm">
                        <strong class="block">{{ __('ui.the_found_animal_is_currently_secured_in_a_safe_place') }}</strong>
                        <span class="mt-1 block text-paw-muted">{{ __('ui.the_exact_address_remains_private_until_ownership_is_verified') }}</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 rounded-md border border-paw-line bg-white p-4">
                    <input type="checkbox" name="safety_acknowledged" value="1" class="mt-1" required @checked(old('safety_acknowledged'))>
                    <span class="text-sm">
                        <strong class="block">{{ __('ui.i_will_keep_exact_addresses_payment_codes_and_sensitive_medical_details_out_of_the_public_description') }}</strong>
                        <span class="mt-1 block text-paw-muted">{{ __('ui.urgent_reports_are_for_coordination_not_emergency_dispatch') }}</span>
                    </span>
                </label>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('lost-found.index') }}" class="action action--surface min-h-11 justify-center">
                        <x-ui-icon name="x" />
                        <span>{{ __('ui.cancel') }}</span>
                    </a>
                    <button type="submit" class="action action--primary min-h-11 justify-center">
                        <x-ui-icon name="siren" />
                        <span>{{ __('ui.publish_urgent_report') }}</span>
                    </button>
                </div>
            </section>
        </form>
    </div>
</x-app-shell>
