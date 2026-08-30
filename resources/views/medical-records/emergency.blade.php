<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-4xl gap-6">
        <header class="medical-emergency-header">
            <div class="flex items-center gap-4">
                @if ($medical_record['image_url'])
                    <img src="{{ $medical_record['image_url'] }}" alt="{{ $medical_record['pet_name'] }}" width="1200" height="900">
                @endif
                <div>
                    <p class="text-sm font-bold uppercase text-paw-coral">{{ __('ui.emergency_health_card') }}</p>
                    <h1 class="mt-1 text-3xl font-bold">{{ $medical_record['pet_name'] }}</h1>
                    <p class="mt-1 text-paw-muted">{{ $medical_record['species'] }} · {{ $medical_record['breed'] }} · {{ $medical_record['age'] }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 print:hidden">
                <x-action-control label="{{ __('ui.full_record') }}" icon="arrow-left" :href="route('medical-records.show', $medical_record['slug'])" />
                <button type="button" class="action action--primary action--compact" data-print-page>
                    <x-ui-icon name="printer" size="sm" />
                    <span>{{ __('ui.print') }}</span>
                </button>
            </div>
        </header>

        <section class="medical-emergency-grid" aria-label="{{ __('ui.emergency_details') }}">
            <div class="medical-emergency-grid__critical">
                <x-ui-icon name="triangle-alert" size="xl" />
                <div>
                    <h2>{{ __('ui.allergies_and_dangerous_reactions') }}</h2>
                    @forelse ($medical_record['critical_allergies'] as $allergy)
                        <p>{{ $allergy }}</p>
                    @empty
                        <p>{{ $medical_record['allergy_knowledge_label'] }}</p>
                    @endforelse
                </div>
            </div>
            <div>
                <h2>{{ __('ui.current_weight') }}</h2>
                <p>{{ $medical_record['current_weight'] }}</p>
            </div>
            <div>
                <h2>{{ __('ui.blood_group') }}</h2>
                <p>{{ $medical_record['blood_group'] ?: __('ui.not_recorded') }}</p>
            </div>
            <div>
                <h2>{{ __('ui.microchip') }}</h2>
                <p>{{ $medical_record['microchip_status'] }}</p>
            </div>
            <div>
                <h2>{{ __('ui.chronic_conditions') }}</h2>
                @forelse ($medical_record['chronic_conditions'] as $condition)
                    <p>{{ $condition }}</p>
                @empty
                    <p>{{ __('ui.none_recorded') }}</p>
                @endforelse
            </div>
        </section>

        <section class="medical-section" aria-labelledby="emergency-medications-title">
            <div class="medical-section__heading">
                <h2 id="emergency-medications-title" class="text-xl font-bold">{{ __('ui.active_medications') }}</h2>
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
                <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.handling_and_contact') }}</p>
                <h2 id="emergency-contact-title" class="mt-1 text-xl font-bold">{{ __('ui.care_instructions') }}</h2>
                <p class="mt-3 leading-7">{{ $medical_record['emergency_notes'] ?: __('ui.no_emergency_handling_note_recorded') }}</p>
            </div>
            <dl>
                <div>
                    <dt>{{ __('ui.primary_clinic') }}</dt>
                    <dd>{{ $medical_record['primary_clinic_name'] ?: __('ui.not_assigned') }}</dd>
                    @if ($medical_record['primary_clinic_contact'])
                        <dd>{{ $medical_record['primary_clinic_contact'] }}</dd>
                    @endif
                </div>
                <div>
                    <dt>{{ __('ui.emergency_contact') }}</dt>
                    <dd>{{ $medical_record['emergency_contact']['name'] ?? __('ui.not_assigned') }}</dd>
                    @if ($medical_record['emergency_contact']['phone'] ?? null)
                        <dd>{{ $medical_record['emergency_contact']['phone'] }}</dd>
                    @endif
                </div>
            </dl>
        </section>

        <footer class="flex flex-col items-start justify-between gap-4 border-t border-paw-line pt-5 sm:flex-row sm:items-center">
            <p class="text-sm text-paw-muted">{{ __('presentation.last_updated_confirm_treatment', ['date' => $updated_label]) }}</p>
            <img src="{{ $qr_code }}" alt="QR code for this emergency health card" width="112" height="112" class="size-28 bg-white p-2">
        </footer>
    </div>
</x-app-shell>
