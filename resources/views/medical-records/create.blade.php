<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-6">
        <x-page-header
            :eyebrow="__('ui.private_from_the_first_save')"
            :title="__('ui.create_a_medical_record')"
            :description="__('ui.health_records')"
            heading-id="create-medical-record-heading"
            :action-label="__('ui.health_records')"
            action-icon="arrow-left"
            :action-href="route('medical-records.index')"
            action-variant="paper"
            data-section="medical-record-create-header"
        />

        @if ($errors->any())
            <div class="medical-form-errors" role="alert">
                <x-ui-icon name="circle-alert" size="lg" />
                <div>
                    <strong>{{ __('ui.check_the_medical_record_details') }}</strong>
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

        @if ($pet_options === [])
            <section class="medical-empty min-h-64">
                <x-ui-icon name="shield-check" size="3xl" />
                <h2 class="text-xl font-bold">{{ __('ui.every_managed_pet_already_has_a_record') }}</h2>
                <x-action-control label="{{ __('ui.open_health_records') }}" icon="arrow-right" variant="primary" :href="route('medical-records.index')" />
            </section>
        @else
            <form method="POST" action="{{ route('medical-records.store') }}" class="grid gap-8">
                @csrf

                <section class="medical-form-section" aria-labelledby="identity-section">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.identity') }}</p>
                        <h2 id="identity-section" class="mt-1 text-xl font-bold">{{ __('ui.pet_and_baseline') }}</h2>
                    </div>
                    <div class="medical-form-grid">
                        <label>
                            {{ __('ui.pet_profile') }}
                            <select name="pet_profile_key" required>
                                @forelse ($pet_options as $value => $label)
                                    <option value="{{ $value }}" @selected(old('pet_profile_key') === $value)>{{ $label }}</option>
                                @empty
                                    <option disabled>{{ __('ui.no_managed_pets') }}</option>
                                @endforelse
                            </select>
                        </label>
                        <label>
                            {{ __('ui.date_of_birth') }}
                            <input type="date" name="birth_date" value="{{ old('birth_date') }}" max="{{ now()->toDateString() }}">
                        </label>
                        <label>
                            {{ __('ui.sex') }}
                            <select name="sex">
                                <option value="unknown">{{ __('ui.unknown') }}</option>
                                <option value="male" @selected(old('sex') === 'male')>{{ __('ui.male') }}</option>
                                <option value="female" @selected(old('sex') === 'female')>{{ __('ui.female') }}</option>
                                <option value="intersex" @selected(old('sex') === 'intersex')>{{ __('ui.intersex_professionally_confirmed') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.reproductive_status') }}
                            <select name="reproductive_status" required>
                                <option value="unknown">{{ __('ui.unknown') }}</option>
                                <option value="intact" @selected(old('reproductive_status') === 'intact')>{{ __('ui.not_sterilized') }}</option>
                                <option value="spayed" @selected(old('reproductive_status') === 'spayed')>{{ __('ui.spayed') }}</option>
                                <option value="neutered" @selected(old('reproductive_status') === 'neutered')>{{ __('ui.neutered') }}</option>
                                <option value="planned" @selected(old('reproductive_status') === 'planned')>{{ __('ui.procedure_planned') }}</option>
                                <option value="medical-restriction" @selected(old('reproductive_status') === 'medical-restriction')>{{ __('ui.medical_restriction') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.current_weight') }}
                            <input type="number" step="0.001" min="0.001" name="weight" value="{{ old('weight') }}">
                        </label>
                        <label>
                            {{ __('ui.weight_unit') }}
                            <select name="weight_unit" required>
                                <option value="kg" @selected(old('weight_unit', 'kg') === 'kg')>{{ __('ui.kilograms') }}</option>
                                <option value="g" @selected(old('weight_unit') === 'g')>{{ __('ui.grams') }}</option>
                                <option value="lb" @selected(old('weight_unit') === 'lb')>{{ __('ui.pounds') }}</option>
                                <option value="oz" @selected(old('weight_unit') === 'oz')>{{ __('ui.ounces') }}</option>
                            </select>
                        </label>
                    </div>
                    <label class="medical-check">
                        <input type="checkbox" name="birth_date_estimated" value="1" @checked(old('birth_date_estimated'))>
                        <span>{{ __('ui.the_birth_date_is_estimated') }}</span>
                    </label>
                </section>

                <section class="medical-form-section" aria-labelledby="identity-medical-section">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.identification') }}</p>
                        <h2 id="identity-medical-section" class="mt-1 text-xl font-bold">{{ __('ui.microchip_and_clinic') }}</h2>
                    </div>
                    <div class="medical-form-grid">
                        <label>
                            {{ __('ui.microchip_status') }}
                            <select name="microchip_status" required>
                                <option value="unknown">{{ __('ui.unknown') }}</option>
                                <option value="registered" @selected(old('microchip_status') === 'registered')>{{ __('ui.registered') }}</option>
                                <option value="present" @selected(old('microchip_status') === 'present')>{{ __('ui.present_registration_not_confirmed') }}</option>
                                <option value="absent" @selected(old('microchip_status') === 'absent')>{{ __('ui.no_microchip') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.microchip_number') }}
                            <input name="microchip_number" value="{{ old('microchip_number') }}" maxlength="80" autocomplete="off">
                        </label>
                        <label>
                            {{ __('ui.last_chip_check') }}
                            <input type="date" name="microchip_checked_on" value="{{ old('microchip_checked_on') }}" max="{{ now()->toDateString() }}">
                        </label>
                        <label>
                            {{ __('ui.blood_group') }}
                            <input name="blood_group" value="{{ old('blood_group') }}" maxlength="60">
                        </label>
                        <label>
                            {{ __('ui.primary_clinic') }}
                            <input name="primary_clinic_name" value="{{ old('primary_clinic_name') }}" maxlength="160">
                        </label>
                        <label>
                            {{ __('ui.clinic_contact') }}
                            <input name="primary_clinic_contact" value="{{ old('primary_clinic_contact') }}" maxlength="500">
                        </label>
                    </div>
                    <input type="hidden" name="timezone" value="{{ $timezone }}">
                </section>

                <section class="medical-form-section" aria-labelledby="critical-section">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-coral">{{ __('ui.critical_information') }}</p>
                        <h2 id="critical-section" class="mt-1 text-xl font-bold">{{ __('ui.allergies_and_emergency_care') }}</h2>
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
                            {{ __('ui.allergies_or_dangerous_reactions') }}
                            <textarea name="critical_allergies" rows="4" maxlength="2000" placeholder="{{ __('ui.one_item_per_line') }}">{{ old('critical_allergies') }}</textarea>
                        </label>
                        <label>
                            {{ __('ui.chronic_conditions') }}
                            <textarea name="chronic_conditions" rows="4" maxlength="3000" placeholder="{{ __('ui.one_item_per_line') }}">{{ old('chronic_conditions') }}</textarea>
                        </label>
                    </div>
                    <label>
                        {{ __('ui.emergency_handling_note') }}
                        <textarea name="emergency_notes" rows="4" maxlength="3000">{{ old('emergency_notes') }}</textarea>
                    </label>
                    <div class="medical-form-grid">
                        <label>
                            {{ __('ui.emergency_contact') }}
                            <input name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" maxlength="120">
                        </label>
                        <label>
                            {{ __('ui.phone') }}
                            <input name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" maxlength="80">
                        </label>
                        <label>
                            {{ __('ui.relationship') }}
                            <input name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', 'owner') }}" maxlength="80">
                        </label>
                    </div>
                </section>

                <section class="flex flex-col gap-4 border-t border-paw-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <label class="medical-check">
                        <input type="checkbox" name="privacy_acknowledged" value="1" required @checked(old('privacy_acknowledged'))>
                        <span>{{ __('ui.i_understand_this_record_stays_private_until_i_issue_specific_access') }}</span>
                    </label>
                    <button type="submit" class="action action--primary action--regular">
                        <x-ui-icon name="shield-plus" />
                        <span>{{ __('ui.create_private_record') }}</span>
                    </button>
                </section>
            </form>
        @endif
    </div>
</x-app-shell>
