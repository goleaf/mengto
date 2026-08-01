<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-4xl gap-6">
        <header class="medical-emergency-header">
            <div class="flex items-center gap-4">
                @if ($medical_record['image_url'])
                    <img src="{{ $medical_record['image_url'] }}" alt="{{ $medical_record['pet_name'] }}">
                @endif
                <div>
                    <p class="text-sm font-bold uppercase text-paw-coral">{{ __('ui.emergency_health_card_738a891cce') }}</p>
                    <h1 class="mt-1 text-3xl font-bold">{{ $medical_record['pet_name'] }}</h1>
                    <p class="mt-1 text-paw-muted">{{ $medical_record['species'] }} · {{ $medical_record['breed'] }} · {{ $medical_record['age'] }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 print:hidden">
                <x-action-control label="{{ __('ui.full_record_09f2fd2709') }}" icon="arrow-left" :href="route('medical-records.show', $medical_record['slug'])" />
                <button type="button" class="action action--primary action--compact" data-print-page>
                    <x-lucide-printer class="icon icon--sm" aria-hidden="true" />
                    <span>{{ __('ui.print_df0fe79898') }}</span>
                </button>
            </div>
        </header>

        <section class="medical-emergency-grid" aria-label="{{ __('ui.emergency_details_f0f1454402') }}">
            <div class="medical-emergency-grid__critical">
                <x-lucide-triangle-alert class="size-6" aria-hidden="true" />
                <div>
                    <h2>{{ __('ui.allergies_and_dangerous_reactions_ff8db657b2') }}</h2>
                    @forelse ($medical_record['critical_allergies'] as $allergy)
                        <p>{{ $allergy }}</p>
                    @empty
                        <p>{{ $medical_record['allergy_knowledge_label'] }}</p>
                    @endforelse
                </div>
            </div>
            <div>
                <h2>{{ __('ui.current_weight_8a05fab730') }}</h2>
                <p>{{ $medical_record['current_weight'] }}</p>
            </div>
            <div>
                <h2>{{ __('ui.blood_group_0cd279b3a6') }}</h2>
                <p>{{ $medical_record['blood_group'] ?: __('ui.not_recorded_b37c7879f6') }}</p>
            </div>
            <div>
                <h2>{{ __('ui.microchip_230fe79bc1') }}</h2>
                <p>{{ $medical_record['microchip_status'] }}</p>
            </div>
            <div>
                <h2>{{ __('ui.chronic_conditions_c3a152a299') }}</h2>
                @forelse ($medical_record['chronic_conditions'] as $condition)
                    <p>{{ $condition }}</p>
                @empty
                    <p>{{ __('ui.none_recorded_ef49549a5e') }}</p>
                @endforelse
            </div>
        </section>

        <section class="medical-section" aria-labelledby="emergency-medications-title">
            <div class="medical-section__heading">
                <h2 id="emergency-medications-title" class="text-xl font-bold">{{ __('ui.active_medications_2a3bd50cbe') }}</h2>
            </div>
            <div class="medical-compact-list">
                @forelse ($medications as $medication)
                    <article>
                        <h3 class="font-bold">{{ $medication['name'] }} · {{ $medication['dose'] }}</h3>
                        <p class="mt-1 text-sm text-paw-muted">{{ $medication['route'] }} · {{ $medication['schedule'] }}</p>
                        @if ($medication['instructions'])
                            <p class="mt-1 text-sm">{{ $medication['instructions'] }}</p>
                        @endif
                    </article>
                @empty
                    <p class="text-sm text-paw-muted">{{ $medical_record['medication_knowledge_label'] }}</p>
                @endforelse
            </div>
        </section>

        <section class="medical-emergency-contact" aria-labelledby="emergency-contact-title">
            <div>
                <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.handling_and_contact_38f8cd9c61') }}</p>
                <h2 id="emergency-contact-title" class="mt-1 text-xl font-bold">{{ __('ui.care_instructions_b37f185847') }}</h2>
                <p class="mt-3 leading-7">{{ $medical_record['emergency_notes'] ?: __('ui.no_emergency_handling_note_recorded_42e3bd2759') }}</p>
            </div>
            <dl>
                <div>
                    <dt>{{ __('ui.primary_clinic_004de405e6') }}</dt>
                    <dd>{{ $medical_record['primary_clinic_name'] ?: __('ui.not_assigned_13075c2336') }}</dd>
                    @if ($medical_record['primary_clinic_contact'])
                        <dd>{{ $medical_record['primary_clinic_contact'] }}</dd>
                    @endif
                </div>
                <div>
                    <dt>{{ __('ui.emergency_contact_9e04489f80') }}</dt>
                    <dd>{{ $medical_record['emergency_contact']['name'] ?? __('ui.not_assigned_13075c2336') }}</dd>
                    @if ($medical_record['emergency_contact']['phone'] ?? null)
                        <dd>{{ $medical_record['emergency_contact']['phone'] }}</dd>
                    @endif
                </div>
            </dl>
        </section>

        <footer class="flex flex-col items-start justify-between gap-4 border-t border-paw-line pt-5 sm:flex-row sm:items-center">
            <p class="text-sm text-paw-muted">{{ __('presentation.last_updated_confirm_treatment', ['date' => $updated_label]) }}</p>
            <img src="{{ $qr_code }}" alt="QR code for this emergency health card" class="size-28 bg-white p-2">
        </footer>
    </div>
</x-app-shell>
