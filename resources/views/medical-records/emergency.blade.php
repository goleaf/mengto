<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-4xl gap-6">
        <header class="medical-emergency-header">
            <div class="flex items-center gap-4">
                @if ($medical_record['image_url'])
                    <img src="{{ $medical_record['image_url'] }}" alt="{{ $medical_record['pet_name'] }}">
                @endif
                <div>
                    <p class="text-sm font-bold uppercase text-paw-coral">Emergency health card</p>
                    <h1 class="mt-1 text-3xl font-bold">{{ $medical_record['pet_name'] }}</h1>
                    <p class="mt-1 text-paw-muted">{{ $medical_record['species'] }} · {{ $medical_record['breed'] }} · {{ $medical_record['age'] }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 print:hidden">
                <x-action-control label="Full record" icon="arrow-left" :href="route('medical-records.show', $medical_record['slug'])" />
                <button type="button" class="action action--primary action--compact" onclick="window.print()">
                    <x-lucide-printer class="icon icon--sm" aria-hidden="true" />
                    <span>Print</span>
                </button>
            </div>
        </header>

        <section class="medical-emergency-grid" aria-label="Emergency details">
            <div class="medical-emergency-grid__critical">
                <x-lucide-triangle-alert class="size-6" aria-hidden="true" />
                <div>
                    <h2>Allergies and dangerous reactions</h2>
                    @forelse ($medical_record['critical_allergies'] as $allergy)
                        <p>{{ $allergy }}</p>
                    @empty
                        <p>No critical allergy recorded.</p>
                    @endforelse
                </div>
            </div>
            <div>
                <h2>Current weight</h2>
                <p>{{ $medical_record['current_weight'] }}</p>
            </div>
            <div>
                <h2>Blood group</h2>
                <p>{{ $medical_record['blood_group'] ?: 'Not recorded' }}</p>
            </div>
            <div>
                <h2>Microchip</h2>
                <p>{{ $medical_record['microchip_status'] }}</p>
            </div>
            <div>
                <h2>Chronic conditions</h2>
                @forelse ($medical_record['chronic_conditions'] as $condition)
                    <p>{{ $condition }}</p>
                @empty
                    <p>None recorded.</p>
                @endforelse
            </div>
        </section>

        <section class="medical-section" aria-labelledby="emergency-medications-title">
            <div class="medical-section__heading">
                <h2 id="emergency-medications-title" class="text-xl font-bold">Active medications</h2>
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
                    <p class="text-sm text-paw-muted">No active medication recorded.</p>
                @endforelse
            </div>
        </section>

        <section class="medical-emergency-contact" aria-labelledby="emergency-contact-title">
            <div>
                <p class="text-xs font-bold uppercase text-paw-leaf">Handling and contact</p>
                <h2 id="emergency-contact-title" class="mt-1 text-xl font-bold">Care instructions</h2>
                <p class="mt-3 leading-7">{{ $medical_record['emergency_notes'] ?: 'No emergency handling note recorded.' }}</p>
            </div>
            <dl>
                <div>
                    <dt>Primary clinic</dt>
                    <dd>{{ $medical_record['primary_clinic_name'] ?: 'Not assigned' }}</dd>
                    @if ($medical_record['primary_clinic_contact'])
                        <dd>{{ $medical_record['primary_clinic_contact'] }}</dd>
                    @endif
                </div>
                <div>
                    <dt>Emergency contact</dt>
                    <dd>{{ $medical_record['emergency_contact']['name'] ?? 'Not assigned' }}</dd>
                    @if ($medical_record['emergency_contact']['phone'] ?? null)
                        <dd>{{ $medical_record['emergency_contact']['phone'] }}</dd>
                    @endif
                </div>
            </dl>
        </section>

        <footer class="flex flex-col items-start justify-between gap-4 border-t border-paw-line pt-5 sm:flex-row sm:items-center">
            <p class="text-sm text-paw-muted">Last updated {{ $updated_label }}. Confirm current treatment with the responsible veterinarian.</p>
            <img src="{{ $qr_code }}" alt="QR code for this emergency health card" class="size-28 bg-white p-2">
        </footer>
    </div>
</x-app-shell>
