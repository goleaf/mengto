<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-6">
        <header class="border-b border-paw-line pb-6">
            <a href="{{ route('lost-found.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-paw-leaf">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                {{ __('ui.lost_found_217c655848') }}
            </a>
            <p class="mt-5 text-sm font-bold uppercase text-paw-coral">{{ __('ui.urgent_report_ef840f5338') }}</p>
            <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('ui.report_a_missing_or_found_animal_d04bc18c2b') }}</h1>
            <p class="mt-3 max-w-3xl leading-7 text-paw-muted">
                {{ __('ui.publish_the_essential_location_and_identification_details_now_c357444423') }}
            </p>
        </header>

        @if ($errors->any())
            <div class="rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-900" role="alert">
                <div class="flex items-center gap-2 font-bold">
                    <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                    {{ __('ui.check_the_highlighted_fields_c350ab0d07') }}
                </div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @forelse ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @empty
                        <li>{{ __('ui.no_validation_details_are_available_bc67f0c5c1') }}</li>
                    @endforelse
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('lost-found.store') }}" enctype="multipart/form-data" class="grid gap-8">
            @csrf
            <input type="hidden" name="intent" value="publish">
            <input type="hidden" name="country" value="LT">

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="report-kind-title">
                <h2 id="report-kind-title" class="text-xl font-bold">{{ __('ui.what_happened_c4dc542b51') }}</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    @forelse ($types as $value => $label)
                        <label class="flex cursor-pointer items-start gap-3 rounded-md border border-paw-line bg-white p-4 has-[:checked]:border-paw-leaf has-[:checked]:bg-paw-mint">
                            <input type="radio" name="type" value="{{ $value }}" class="mt-1" @checked(old('type', 'lost') === $value)>
                            <span>
                                <strong class="block">{{ $label }}</strong>
                                <span class="mt-1 block text-sm text-paw-muted">
                                    {{ $value === 'lost' ? __('ui.start_an_active_search_from_a_pet_profile_7c530ff322') : __('ui.create_a_protected_handover_and_owner_verification_path_4b32d22867') }}
                                </span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-paw-muted">{{ __('ui.report_types_are_unavailable_3afd910a90') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="animal-title">
                <h2 id="animal-title" class="text-xl font-bold">{{ __('ui.animal_details_a8e52c403a') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.pet_profile_fc2c49bb42') }}
                        <select name="pet_profile_key" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">{{ __('ui.no_saved_profile_af8b04d165') }}</option>
                            @forelse ($pet_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('pet_profile_key', 'scout') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_profiles_c5b4331f0e') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.name_dcd1d5223f') }}
                        <input name="pet_name" value="{{ old('pet_name', $default_pet['name'] ?? '') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="100">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.species_56205e12c2') }}
                        <select name="species" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                            @forelse ($species_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('species', 'dog') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_species_7079df2086') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.breed_or_mix_391790c8a0') }}
                        <input name="breed" value="{{ old('breed', $default_pet['breed'] ?? '') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="120">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.primary_color_2b03958ca7') }}
                        <input name="primary_color" value="{{ old('primary_color', 'black with white chest') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="80">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.size_1af8519073') }}
                        <select name="size" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">{{ __('ui.choose_size_03f6ea82bd') }}</option>
                            @forelse ($size_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('size', 'large') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_size_options_60cfd1979a') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.sex_953dd6f2b4') }}
                        <select name="sex" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">{{ __('ui.unknown_b764cdc0ea') }}</option>
                            <option value="male" @selected(old('sex') === 'male')>{{ __('ui.male_03f8c1273e') }}</option>
                            <option value="female" @selected(old('sex') === 'female')>{{ __('ui.female_e8cca808ae') }}</option>
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.age_39b7370f30') }}
                        <input name="age_label" value="{{ old('age_label', $default_pet['age'] ?? '') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="80" placeholder="{{ __('ui.4_years_cfd73a0bc4') }}">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.microchip_230fe79bc1') }}
                        <select name="microchip_status" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($microchip_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('microchip_status', 'present') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_microchip_options_9f87ae5553') }}</option>
                            @endforelse
                        </select>
                    </label>
                </div>

                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.public_description_05a15f0699') }}
                    <textarea name="description" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="3000">{{ old('description', __('ui.friendly_at_home_but_may_run_if_approached_f06276fae2')) }}</textarea>
                </label>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.visible_identifying_marks_af2c08c3aa') }}
                        <textarea name="distinctive_marks" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1500">{{ old('distinctive_marks') }}</textarea>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.hidden_verification_mark_dc19890619') }}
                        <textarea name="hidden_marks" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1000">{{ old('hidden_marks') }}</textarea>
                        <span class="text-xs font-normal text-paw-muted">{{ __('ui.visible_only_to_the_search_coordinator_0958eb7e0d') }}</span>
                    </label>
                </div>

                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.current_photos_2b2ce7b708') }}
                    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    <span class="text-xs font-normal text-paw-muted">{{ __('ui.up_to_six_jpg_png_or_webp_files_943f8f7f98') }}</span>
                </label>
            </section>

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="location-title">
                <h2 id="location-title" class="text-xl font-bold">{{ __('ui.last_location_and_time_bce5be2bf2') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.public_area_911f5d1f74') }}
                        <input name="last_seen_area" value="{{ old('last_seen_area') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="160" placeholder="{{ __('ui.vingis_park_western_entrance_4f18c7ed83') }}">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.city_fc33f73246') }}
                        <input name="city" value="{{ old('city', __('ui.vilnius_c283e0869a')) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="100">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.observed_at_2f4add0f44') }}
                        <input type="datetime-local" name="last_seen_at" value="{{ old('last_seen_at', now()->subMinutes(30)->format('Y-m-d\TH:i')) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.direction_9c8a9579ab') }}
                        <input name="direction" value="{{ old('direction') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="100" placeholder="{{ __('ui.toward_the_river_path_736a270712') }}">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.exact_latitude_27be947b0b') }}
                        <input type="number" step="0.000001" name="latitude" value="{{ old('latitude', '54.683400') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.exact_longitude_a1f25ad693') }}
                        <input type="number" step="0.000001" name="longitude" value="{{ old('longitude', '25.236800') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2">
                        {{ __('ui.exact_location_note_fc4b4ef378') }}
                        <input name="location_note" value="{{ old('location_note') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="300" placeholder="{{ __('ui.bench_beside_the_west_gate_b1e3660f18') }}">
                        <span class="text-xs font-normal text-paw-muted">{{ __('ui.encrypted_and_limited_to_the_search_team_public_9b543a68bd') }}</span>
                    </label>
                </div>
            </section>

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="safety-title">
                <h2 id="safety-title" class="text-xl font-bold">{{ __('ui.safe_approach_729020bb30') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.what_to_do_f47f1edc44') }}
                        <textarea name="approach_instructions" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1500">{{ old('approach_instructions', __('ui.stay_at_a_distance_speak_quietly_note_the_34b218d3d8')) }}</textarea>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.what_to_avoid_48d524c96f') }}
                        <textarea name="avoid_instructions" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1500">{{ old('avoid_instructions', __('ui.do_not_chase_surround_shout_or_enter_unsafe_b85df37377')) }}</textarea>
                    </label>
                </div>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.health_or_immediate_safety_notice_d6589ad74e') }}
                    <input name="health_notice" value="{{ old('health_notice') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1000" placeholder="{{ __('ui.needs_regular_medication_contact_the_owner_quickly_236b3691a8') }}">
                </label>
            </section>

            <section class="grid gap-4" aria-labelledby="contact-title">
                <h2 id="contact-title" class="text-xl font-bold">{{ __('ui.protected_contact_and_alerts_f69a26837e') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.contact_channel_adba6560ed') }}
                        <select name="contact_channel" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="platform" @selected(old('contact_channel', 'platform') === 'platform')>{{ __('ui.protected_platform_messages_eb4c003025') }}</option>
                            <option value="email" @selected(old('contact_channel') === 'email')>{{ __('ui.protected_email_964d7a443f') }}</option>
                            <option value="phone" @selected(old('contact_channel') === 'phone')>{{ __('ui.protected_phone_98b8ed183c') }}</option>
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.email_or_phone_when_selected_f881102f55') }}
                        <input name="contact_value" value="{{ old('contact_value') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="160">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.alert_radius_4f78cfba5d') }}
                        <select name="notification_radius_km" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ([2, 5, 10, 25, 50] as $radius)
                                <option value="{{ $radius }}" @selected((int) old('notification_radius_km', 5) === $radius)>{{ __('presentation.kilometers', ['count' => $radius]) }}</option>
                            @empty
                                <option value="">{{ __('ui.no_radius_options_available_e1e895c158') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.visibility_7448611d5f') }}
                        <select name="visibility" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="public" @selected(old('visibility', 'public') === 'public')>{{ __('ui.public_directory_2b6c036f1f') }}</option>
                            <option value="link" @selected(old('visibility') === 'link')>{{ __('ui.accessible_by_link_d7a7cd887b') }}</option>
                            <option value="registered" @selected(old('visibility') === 'registered')>{{ __('ui.registered_community_41422b19f4') }}</option>
                            <option value="local-group" @selected(old('visibility') === 'local-group')>{{ __('ui.local_group_only_6829d1d78d') }}</option>
                        </select>
                    </label>
                </div>

                <label class="flex items-start gap-3 rounded-md border border-paw-line bg-white p-4">
                    <input type="checkbox" name="animal_secured" value="1" class="mt-1" @checked(old('animal_secured'))>
                    <span class="text-sm">
                        <strong class="block">{{ __('ui.the_found_animal_is_currently_secured_in_a_b5a37e271d') }}</strong>
                        <span class="mt-1 block text-paw-muted">{{ __('ui.the_exact_address_remains_private_until_ownership_is_ebcd464bc9') }}</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 rounded-md border border-paw-line bg-white p-4">
                    <input type="checkbox" name="safety_acknowledged" value="1" class="mt-1" required @checked(old('safety_acknowledged'))>
                    <span class="text-sm">
                        <strong class="block">{{ __('ui.i_will_keep_exact_addresses_payment_codes_and_d403fb78d8') }}</strong>
                        <span class="mt-1 block text-paw-muted">{{ __('ui.urgent_reports_are_for_coordination_not_emergency_dispatch_858978b7ac') }}</span>
                    </span>
                </label>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('lost-found.index') }}" class="action action--surface">{{ __('ui.cancel_19766ed6cc') }}</a>
                    <button type="submit" class="action action--primary">
                        <x-lucide-siren class="icon" aria-hidden="true" />
                        <span>{{ __('ui.publish_urgent_report_1de0655879') }}</span>
                    </button>
                </div>
            </section>
        </form>
    </div>
</x-app-shell>
