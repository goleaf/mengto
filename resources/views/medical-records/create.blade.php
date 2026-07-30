<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-6">
        <header class="border-b border-paw-line pb-6">
            <a href="{{ route('medical-records.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-paw-leaf">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                Health records
            </a>
            <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">Private from the first save</p>
            <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Create a medical record</h1>
        </header>

        @if ($errors->any())
            <div class="medical-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>Check the medical record details</strong>
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

        @if ($pet_options === [])
            <section class="medical-empty min-h-64">
                <x-lucide-shield-check class="size-9" aria-hidden="true" />
                <h2 class="text-xl font-bold">Every managed pet already has a record</h2>
                <x-action-control label="Open health records" icon="arrow-right" variant="primary" :href="route('medical-records.index')" />
            </section>
        @else
            <form method="POST" action="{{ route('medical-records.store') }}" class="grid gap-8">
                @csrf

                <section class="medical-form-section" aria-labelledby="identity-section">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">Identity</p>
                        <h2 id="identity-section" class="mt-1 text-xl font-bold">Pet and baseline</h2>
                    </div>
                    <div class="medical-form-grid">
                        <label>
                            Pet profile
                            <select name="pet_profile_key" required>
                                @forelse ($pet_options as $value => $label)
                                    <option value="{{ $value }}" @selected(old('pet_profile_key') === $value)>{{ $label }}</option>
                                @empty
                                    <option disabled>No managed pets</option>
                                @endforelse
                            </select>
                        </label>
                        <label>
                            Date of birth
                            <input type="date" name="birth_date" value="{{ old('birth_date') }}" max="{{ now()->toDateString() }}">
                        </label>
                        <label>
                            Sex
                            <select name="sex">
                                <option value="unknown">Unknown</option>
                                <option value="male" @selected(old('sex') === 'male')>Male</option>
                                <option value="female" @selected(old('sex') === 'female')>Female</option>
                                <option value="intersex" @selected(old('sex') === 'intersex')>Intersex, professionally confirmed</option>
                            </select>
                        </label>
                        <label>
                            Reproductive status
                            <select name="reproductive_status" required>
                                <option value="unknown">Unknown</option>
                                <option value="intact" @selected(old('reproductive_status') === 'intact')>Not sterilized</option>
                                <option value="spayed" @selected(old('reproductive_status') === 'spayed')>Spayed</option>
                                <option value="neutered" @selected(old('reproductive_status') === 'neutered')>Neutered</option>
                                <option value="planned" @selected(old('reproductive_status') === 'planned')>Procedure planned</option>
                                <option value="medical-restriction" @selected(old('reproductive_status') === 'medical-restriction')>Medical restriction</option>
                            </select>
                        </label>
                        <label>
                            Current weight
                            <input type="number" step="0.001" min="0.001" name="weight" value="{{ old('weight') }}">
                        </label>
                        <label>
                            Weight unit
                            <select name="weight_unit" required>
                                <option value="kg" @selected(old('weight_unit', 'kg') === 'kg')>Kilograms</option>
                                <option value="g" @selected(old('weight_unit') === 'g')>Grams</option>
                                <option value="lb" @selected(old('weight_unit') === 'lb')>Pounds</option>
                                <option value="oz" @selected(old('weight_unit') === 'oz')>Ounces</option>
                            </select>
                        </label>
                    </div>
                    <label class="medical-check">
                        <input type="checkbox" name="birth_date_estimated" value="1" @checked(old('birth_date_estimated'))>
                        <span>The birth date is estimated</span>
                    </label>
                </section>

                <section class="medical-form-section" aria-labelledby="identity-medical-section">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">Identification</p>
                        <h2 id="identity-medical-section" class="mt-1 text-xl font-bold">Microchip and clinic</h2>
                    </div>
                    <div class="medical-form-grid">
                        <label>
                            Microchip status
                            <select name="microchip_status" required>
                                <option value="unknown">Unknown</option>
                                <option value="registered" @selected(old('microchip_status') === 'registered')>Registered</option>
                                <option value="present" @selected(old('microchip_status') === 'present')>Present, registration not confirmed</option>
                                <option value="absent" @selected(old('microchip_status') === 'absent')>No microchip</option>
                            </select>
                        </label>
                        <label>
                            Microchip number
                            <input name="microchip_number" value="{{ old('microchip_number') }}" maxlength="80" autocomplete="off">
                        </label>
                        <label>
                            Last chip check
                            <input type="date" name="microchip_checked_on" value="{{ old('microchip_checked_on') }}" max="{{ now()->toDateString() }}">
                        </label>
                        <label>
                            Blood group
                            <input name="blood_group" value="{{ old('blood_group') }}" maxlength="60">
                        </label>
                        <label>
                            Primary clinic
                            <input name="primary_clinic_name" value="{{ old('primary_clinic_name') }}" maxlength="160">
                        </label>
                        <label>
                            Clinic contact
                            <input name="primary_clinic_contact" value="{{ old('primary_clinic_contact') }}" maxlength="500">
                        </label>
                    </div>
                    <input type="hidden" name="timezone" value="{{ $timezone }}">
                </section>

                <section class="medical-form-section" aria-labelledby="critical-section">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-coral">Critical information</p>
                        <h2 id="critical-section" class="mt-1 text-xl font-bold">Allergies and emergency care</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label>
                            Allergies or dangerous reactions
                            <textarea name="critical_allergies" rows="4" maxlength="2000" placeholder="One item per line">{{ old('critical_allergies') }}</textarea>
                        </label>
                        <label>
                            Chronic conditions
                            <textarea name="chronic_conditions" rows="4" maxlength="3000" placeholder="One item per line">{{ old('chronic_conditions') }}</textarea>
                        </label>
                    </div>
                    <label>
                        Emergency handling note
                        <textarea name="emergency_notes" rows="4" maxlength="3000">{{ old('emergency_notes') }}</textarea>
                    </label>
                    <div class="medical-form-grid">
                        <label>
                            Emergency contact
                            <input name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" maxlength="120">
                        </label>
                        <label>
                            Phone
                            <input name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" maxlength="80">
                        </label>
                        <label>
                            Relationship
                            <input name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', 'owner') }}" maxlength="80">
                        </label>
                    </div>
                </section>

                <section class="flex flex-col gap-4 border-t border-paw-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <label class="medical-check">
                        <input type="checkbox" name="privacy_acknowledged" value="1" required @checked(old('privacy_acknowledged'))>
                        <span>I understand this record stays private until I issue specific access.</span>
                    </label>
                    <button type="submit" class="action action--primary action--regular">
                        <x-lucide-shield-plus class="icon" aria-hidden="true" />
                        <span>Create private record</span>
                    </button>
                </section>
            </form>
        @endif
    </div>
</x-app-shell>
