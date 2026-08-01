<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-6">
        <header class="border-b border-paw-line pb-6">
            <a href="{{ route('medical-records.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-paw-leaf">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                {{ __('ui.health_records_bd13778e4d') }}
            </a>
            <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">{{ __('ui.private_from_the_first_save_297f934e26') }}</p>
            <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('ui.create_a_medical_record_bff5a73bf2') }}</h1>
        </header>

        @if ($errors->any())
            <div class="medical-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>{{ __('ui.check_the_medical_record_details_f8a00d6234') }}</strong>
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

        @if ($pet_options === [])
            <section class="medical-empty min-h-64">
                <x-lucide-shield-check class="size-9" aria-hidden="true" />
                <h2 class="text-xl font-bold">{{ __('ui.every_managed_pet_already_has_a_record_6e9fd78fcf') }}</h2>
                <x-action-control label="{{ __('ui.open_health_records_80502ab69c') }}" icon="arrow-right" variant="primary" :href="route('medical-records.index')" />
            </section>
        @else
            <form method="POST" action="{{ route('medical-records.store') }}" class="grid gap-8">
                @csrf

                <section class="medical-form-section" aria-labelledby="identity-section">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.identity_999f23fcd7') }}</p>
                        <h2 id="identity-section" class="mt-1 text-xl font-bold">{{ __('ui.pet_and_baseline_07cac54853') }}</h2>
                    </div>
                    <div class="medical-form-grid">
                        <label>
                            {{ __('ui.pet_profile_fc2c49bb42') }}
                            <select name="pet_profile_key" required>
                                @forelse ($pet_options as $value => $label)
                                    <option value="{{ $value }}" @selected(old('pet_profile_key') === $value)>{{ $label }}</option>
                                @empty
                                    <option disabled>{{ __('ui.no_managed_pets_f01cb5694e') }}</option>
                                @endforelse
                            </select>
                        </label>
                        <label>
                            {{ __('ui.date_of_birth_eb4c4e2391') }}
                            <input type="date" name="birth_date" value="{{ old('birth_date') }}" max="{{ now()->toDateString() }}">
                        </label>
                        <label>
                            {{ __('ui.sex_953dd6f2b4') }}
                            <select name="sex">
                                <option value="unknown">{{ __('ui.unknown_b764cdc0ea') }}</option>
                                <option value="male" @selected(old('sex') === 'male')>{{ __('ui.male_03f8c1273e') }}</option>
                                <option value="female" @selected(old('sex') === 'female')>{{ __('ui.female_e8cca808ae') }}</option>
                                <option value="intersex" @selected(old('sex') === 'intersex')>{{ __('ui.intersex_professionally_confirmed_2396c49d43') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.reproductive_status_86a1af9a48') }}
                            <select name="reproductive_status" required>
                                <option value="unknown">{{ __('ui.unknown_b764cdc0ea') }}</option>
                                <option value="intact" @selected(old('reproductive_status') === 'intact')>{{ __('ui.not_sterilized_e5bc0fd668') }}</option>
                                <option value="spayed" @selected(old('reproductive_status') === 'spayed')>{{ __('ui.spayed_14a1e7cd1c') }}</option>
                                <option value="neutered" @selected(old('reproductive_status') === 'neutered')>{{ __('ui.neutered_65bac8fc67') }}</option>
                                <option value="planned" @selected(old('reproductive_status') === 'planned')>{{ __('ui.procedure_planned_4c06af88ab') }}</option>
                                <option value="medical-restriction" @selected(old('reproductive_status') === 'medical-restriction')>{{ __('ui.medical_restriction_ecd2f3ac82') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.current_weight_8a05fab730') }}
                            <input type="number" step="0.001" min="0.001" name="weight" value="{{ old('weight') }}">
                        </label>
                        <label>
                            {{ __('ui.weight_unit_d9f321f3b3') }}
                            <select name="weight_unit" required>
                                <option value="kg" @selected(old('weight_unit', 'kg') === 'kg')>{{ __('ui.kilograms_1bb53a5e14') }}</option>
                                <option value="g" @selected(old('weight_unit') === 'g')>{{ __('ui.grams_0d50efff06') }}</option>
                                <option value="lb" @selected(old('weight_unit') === 'lb')>{{ __('ui.pounds_b48176ac41') }}</option>
                                <option value="oz" @selected(old('weight_unit') === 'oz')>{{ __('ui.ounces_adf03b72d3') }}</option>
                            </select>
                        </label>
                    </div>
                    <label class="medical-check">
                        <input type="checkbox" name="birth_date_estimated" value="1" @checked(old('birth_date_estimated'))>
                        <span>{{ __('ui.the_birth_date_is_estimated_d8d982bca9') }}</span>
                    </label>
                </section>

                <section class="medical-form-section" aria-labelledby="identity-medical-section">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.identification_91f606b32d') }}</p>
                        <h2 id="identity-medical-section" class="mt-1 text-xl font-bold">{{ __('ui.microchip_and_clinic_919c1bec17') }}</h2>
                    </div>
                    <div class="medical-form-grid">
                        <label>
                            {{ __('ui.microchip_status_797d361281') }}
                            <select name="microchip_status" required>
                                <option value="unknown">{{ __('ui.unknown_b764cdc0ea') }}</option>
                                <option value="registered" @selected(old('microchip_status') === 'registered')>{{ __('ui.registered_2351ee9926') }}</option>
                                <option value="present" @selected(old('microchip_status') === 'present')>{{ __('ui.present_registration_not_confirmed_11f78245a8') }}</option>
                                <option value="absent" @selected(old('microchip_status') === 'absent')>{{ __('ui.no_microchip_3cfcd14a7c') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.microchip_number_ae057dc810') }}
                            <input name="microchip_number" value="{{ old('microchip_number') }}" maxlength="80" autocomplete="off">
                        </label>
                        <label>
                            {{ __('ui.last_chip_check_93437b0aae') }}
                            <input type="date" name="microchip_checked_on" value="{{ old('microchip_checked_on') }}" max="{{ now()->toDateString() }}">
                        </label>
                        <label>
                            {{ __('ui.blood_group_0cd279b3a6') }}
                            <input name="blood_group" value="{{ old('blood_group') }}" maxlength="60">
                        </label>
                        <label>
                            {{ __('ui.primary_clinic_004de405e6') }}
                            <input name="primary_clinic_name" value="{{ old('primary_clinic_name') }}" maxlength="160">
                        </label>
                        <label>
                            {{ __('ui.clinic_contact_160fad500b') }}
                            <input name="primary_clinic_contact" value="{{ old('primary_clinic_contact') }}" maxlength="500">
                        </label>
                    </div>
                    <input type="hidden" name="timezone" value="{{ $timezone }}">
                </section>

                <section class="medical-form-section" aria-labelledby="critical-section">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-coral">{{ __('ui.critical_information_c3f0982cd5') }}</p>
                        <h2 id="critical-section" class="mt-1 text-xl font-bold">{{ __('ui.allergies_and_emergency_care_ac979fcc44') }}</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label>
                            {{ __('medical.fields.allergy_knowledge_status') }}
                            <select name="allergy_knowledge_status" required>
                                @forelse ($knowledge_status_options as $value => $label)
                                    <option value="{{ $value }}" @selected(old('allergy_knowledge_status', 'unknown') === $value)>{{ $label }}</option>
                                @empty
                                    <option value="unknown">{{ __('medical.knowledge_statuses.unknown') }}</option>
                                @endforelse
                            </select>
                        </label>
                        <label>
                            {{ __('medical.fields.medication_knowledge_status') }}
                            <select name="medication_knowledge_status" required>
                                @forelse ($knowledge_status_options as $value => $label)
                                    <option value="{{ $value }}" @selected(old('medication_knowledge_status', 'unknown') === $value)>{{ $label }}</option>
                                @empty
                                    <option value="unknown">{{ __('medical.knowledge_statuses.unknown') }}</option>
                                @endforelse
                            </select>
                        </label>
                        <label>
                            {{ __('ui.allergies_or_dangerous_reactions_5eefd62ea1') }}
                            <textarea name="critical_allergies" rows="4" maxlength="2000" placeholder="{{ __('ui.one_item_per_line_a1a8b0c7a9') }}">{{ old('critical_allergies') }}</textarea>
                        </label>
                        <label>
                            {{ __('ui.chronic_conditions_c3a152a299') }}
                            <textarea name="chronic_conditions" rows="4" maxlength="3000" placeholder="{{ __('ui.one_item_per_line_a1a8b0c7a9') }}">{{ old('chronic_conditions') }}</textarea>
                        </label>
                    </div>
                    <label>
                        {{ __('ui.emergency_handling_note_d75afeca08') }}
                        <textarea name="emergency_notes" rows="4" maxlength="3000">{{ old('emergency_notes') }}</textarea>
                    </label>
                    <div class="medical-form-grid">
                        <label>
                            {{ __('ui.emergency_contact_9e04489f80') }}
                            <input name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" maxlength="120">
                        </label>
                        <label>
                            {{ __('ui.phone_63dceb8800') }}
                            <input name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" maxlength="80">
                        </label>
                        <label>
                            {{ __('ui.relationship_25490a10d0') }}
                            <input name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', 'owner') }}" maxlength="80">
                        </label>
                    </div>
                </section>

                <section class="flex flex-col gap-4 border-t border-paw-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <label class="medical-check">
                        <input type="checkbox" name="privacy_acknowledged" value="1" required @checked(old('privacy_acknowledged'))>
                        <span>{{ __('ui.i_understand_this_record_stays_private_until_i_0a278fba19') }}</span>
                    </label>
                    <button type="submit" class="action action--primary action--regular">
                        <x-lucide-shield-plus class="icon" aria-hidden="true" />
                        <span>{{ __('ui.create_private_record_6d280f9113') }}</span>
                    </button>
                </section>
            </form>
        @endif
    </div>
</x-app-shell>
