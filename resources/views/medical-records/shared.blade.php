<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-7">
        <header class="medical-shared-header">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge label="{{ __('ui.temporary_access_7059688673') }}" icon="key-round" tone="success" />
                    <x-status-badge :label="$grant['recipient_role']" icon="user-round-check" tone="surface" />
                </div>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">{{ $medical_record['pet_name'] }}</h1>
                <p class="mt-2 text-paw-muted">{{ $medical_record['species'] }} · {{ $medical_record['breed'] }}</p>
            </div>
            <dl>
                <div><dt>{{ __('ui.access_ec5ba0abb7') }}</dt><dd>{{ $grant['label'] }}</dd></div>
                <div><dt>{{ __('ui.expires_f6725f3af0') }}</dt><dd>{{ $grant['expires_at'] }}</dd></div>
                <div><dt>{{ __('ui.opens_left_a4c0eb453a') }}</dt><dd>{{ $grant['views_remaining'] }}</dd></div>
            </dl>
        </header>

        @if (array_intersect(['summary', 'emergency'], $grant['sections']) !== [])
            <x-medical-critical-summary :record="[
                ...$medical_record,
                'critical_allergies' => $medical_record['critical_allergies'] ?? [],
                'chronic_conditions' => $medical_record['chronic_conditions'] ?? [],
                'emergency_notes' => $medical_record['emergency_notes'] ?? null,
                'primary_clinic_name' => $medical_record['primary_clinic_name'] ?? null,
                'primary_clinic_contact' => $medical_record['primary_clinic_contact'] ?? null,
            ]" />
        @endif

        @if (isset($medical_record['current_weight']) || isset($medical_record['microchip_status']))
            <section class="medical-summary-grid" aria-label="{{ __('ui.shared_health_summary_f4e27528e2') }}">
                @isset($medical_record['current_weight'])
                    <div><x-lucide-scale class="size-5 text-paw-leaf" aria-hidden="true" /><span>{{ __('ui.current_weight_8a05fab730') }}</span><strong>{{ $medical_record['current_weight'] }}</strong></div>
                @endisset
                @isset($medical_record['microchip_status'])
                    <div><x-lucide-scan-line class="size-5 text-paw-leaf" aria-hidden="true" /><span>{{ __('ui.microchip_230fe79bc1') }}</span><strong>{{ $medical_record['microchip_masked'] ?? $medical_record['microchip_status'] }}</strong></div>
                @endisset
                @isset($medical_record['blood_group'])
                    <div><x-lucide-droplets class="size-5 text-paw-leaf" aria-hidden="true" /><span>{{ __('ui.blood_group_0cd279b3a6') }}</span><strong>{{ $medical_record['blood_group'] ?: __('ui.not_recorded_b37c7879f6') }}</strong></div>
                @endisset
            </section>
        @endif

        @if ($medications !== [])
            <section class="medical-section" aria-labelledby="shared-medications-title">
                <div class="medical-section__heading">
                    <h2 id="shared-medications-title" class="text-xl font-bold">{{ __('ui.active_medications_2a3bd50cbe') }}</h2>
                </div>
                <div class="medical-compact-list">
                    @forelse ($medications as $medication)
                        <article>
                            <h3 class="font-bold">{{ $medication['name'] }} · {{ $medication['dose'] }}</h3>
                            <p class="mt-1 text-sm text-paw-muted">{{ $medication['route'] }} · {{ $medication['schedule'] }}</p>
                            @if ($medication['instructions'])
                                <p class="mt-2 text-sm leading-6">{{ $medication['instructions'] }}</p>
                            @endif
                            <p class="mt-2 text-xs font-semibold text-paw-muted">{{ $medication['verification'] }}</p>
                        </article>
                    @empty
                    @endforelse
                </div>
            </section>
        @endif

        @if ($vaccinations !== [])
            <section class="medical-section" aria-labelledby="shared-vaccinations-title">
                <div class="medical-section__heading">
                    <h2 id="shared-vaccinations-title" class="text-xl font-bold">{{ __('ui.vaccinations_ed3861e631') }}</h2>
                </div>
                <div class="medical-compact-list">
                    @forelse ($vaccinations as $vaccination)
                        <article>
                            <h3 class="font-bold">{{ $vaccination['name'] }}</h3>
                            <p class="mt-1 text-sm text-paw-muted">{{ __('presentation.given_on_next', ['given' => $vaccination['administered_on'] ?: __('ui.not_recorded_b37c7879f6'), 'next' => $vaccination['next_due_on'] ?: __('ui.not_set_4895f73177')]) }}</p>
                            <p class="mt-1 text-xs font-semibold">{{ $vaccination['verification'] }}</p>
                        </article>
                    @empty
                    @endforelse
                </div>
            </section>
        @endif

        @if ($weights !== [])
            <section class="medical-section" aria-labelledby="shared-weight-title">
                <div class="medical-section__heading">
                    <h2 id="shared-weight-title" class="text-xl font-bold">{{ __('ui.weight_history_a1ea27c673') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="medical-table min-w-[34rem]">
                        <thead><tr><th>{{ __('ui.date_99c40ab405') }}</th><th>{{ __('ui.weight_81d27ef6d5') }}</th><th>{{ __('ui.source_0e570ca6fa') }}</th></tr></thead>
                        <tbody>
                            @forelse ($weights as $weight)
                                <tr><td>{{ $weight['measured_at'] }}</td><td class="font-bold">{{ $weight['weight'] }}</td><td>{{ $weight['source_name'] }}<span>{{ $weight['verification'] }}</span></td></tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($events !== [])
            <x-medical-timeline :events="$events" />
        @endif

        @if ($documents !== [])
            <section class="medical-section" aria-labelledby="shared-documents-title">
                <div class="medical-section__heading">
                    <div>
                        <h2 id="shared-documents-title" class="text-xl font-bold">{{ __('ui.documents_b4e929d8bc') }}</h2>
                        <p class="mt-1 text-sm text-paw-muted">{{ $grant['allow_download'] ? __('ui.downloads_are_enabled_for_this_link_3a1067aa8b') : __('ui.this_link_is_view_only_795953827b') }}</p>
                    </div>
                </div>
                <x-medical-document-list :documents="$documents" />
            </section>
        @endif

        @if ($reminders !== [])
            <section class="medical-section" aria-labelledby="shared-reminders-title">
                <div class="medical-section__heading"><h2 id="shared-reminders-title" class="text-xl font-bold">{{ __('ui.upcoming_reminders_2ca835d9e9') }}</h2></div>
                <div class="medical-compact-list">
                    @forelse ($reminders as $reminder)
                        <article><h3 class="font-bold">{{ $reminder['title'] }}</h3><p class="mt-1 text-sm text-paw-muted">{{ $reminder['due_at'] }} · {{ $reminder['priority'] }}</p></article>
                    @empty
                    @endforelse
                </div>
            </section>
        @endif

        <footer class="medical-privacy-strip">
            <x-lucide-shield-check class="size-5" aria-hidden="true" />
            <div>
                <strong>{{ __('ui.access_is_limited_to_the_sections_above_bdb4fa2910') }}</strong>
                <span>{{ __('ui.the_owner_can_revoke_this_link_at_any_bf4eca8fd2') }}</span>
            </div>
        </footer>
    </div>
</x-app-shell>
