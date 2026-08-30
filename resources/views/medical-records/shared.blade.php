<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-7">
        <header class="medical-shared-header">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge label="{{ __('ui.temporary_access') }}" icon="key-round" tone="success" />
                    <x-status-badge :label="$grant['recipient_role']" icon="user-round-check" tone="surface" />
                </div>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">{{ $medical_record['pet_name'] }}</h1>
                <p class="mt-2 text-paw-muted">{{ $medical_record['species'] }} · {{ $medical_record['breed'] }}</p>
            </div>
            <dl>
                <div><dt>{{ __('ui.access') }}</dt><dd>{{ $grant['label'] }}</dd></div>
                <div><dt>{{ __('ui.expires') }}</dt><dd>{{ $grant['expires_at'] }}</dd></div>
                <div><dt>{{ __('ui.opens_left') }}</dt><dd>{{ $grant['views_remaining'] }}</dd></div>
            </dl>
        </header>

        @if (array_intersect(['summary', 'emergency'], $grant['sections']) !== [])
            <x-medical-critical-summary :record="[
                ...$medical_record,
                'critical_allergies' => $medical_record['critical_allergies'] ?? [],
                'allergy_knowledge_label' => $medical_record['allergy_knowledge_label'] ?? __('medical.knowledge_statuses.unknown'),
                'chronic_conditions' => $medical_record['chronic_conditions'] ?? [],
                'emergency_notes' => $medical_record['emergency_notes'] ?? null,
                'primary_clinic_name' => $medical_record['primary_clinic_name'] ?? null,
                'primary_clinic_contact' => $medical_record['primary_clinic_contact'] ?? null,
            ]" />
        @endif

        @if (isset($medical_record['current_weight']) || isset($medical_record['microchip_status']))
            <section class="medical-summary-grid" aria-label="{{ __('ui.shared_health_summary') }}">
                @isset($medical_record['current_weight'])
                    <div><x-ui-icon name="scale" size="lg" class="text-paw-leaf" /><span>{{ __('ui.current_weight') }}</span><strong>{{ $medical_record['current_weight'] }}</strong></div>
                @endisset
                @isset($medical_record['microchip_status'])
                    <div><x-ui-icon name="scan-line" size="lg" class="text-paw-leaf" /><span>{{ __('ui.microchip') }}</span><strong>{{ $medical_record['microchip_masked'] ?? $medical_record['microchip_status'] }}</strong></div>
                @endisset
                @isset($medical_record['blood_group'])
                    <div><x-ui-icon name="droplets" size="lg" class="text-paw-leaf" /><span>{{ __('ui.blood_group') }}</span><strong>{{ $medical_record['blood_group'] ?: __('ui.not_recorded') }}</strong></div>
                @endisset
            </section>
        @endif

        @if (array_intersect(['medications', 'emergency'], $grant['sections']) !== [])
            <section class="medical-section" aria-labelledby="shared-medications-title">
                <div class="medical-section__heading">
                    <h2 id="shared-medications-title" class="text-xl font-bold">{{ __('ui.active_medications') }}</h2>
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
                        <p class="text-sm text-paw-muted">{{ $medical_record['medication_knowledge_label'] ?? __('medical.knowledge_statuses.unknown') }}</p>
                    @endforelse
                </div>
            </section>
        @endif

        @if ($vaccinations !== [])
            <section class="medical-section" aria-labelledby="shared-vaccinations-title">
                <div class="medical-section__heading">
                    <h2 id="shared-vaccinations-title" class="text-xl font-bold">{{ __('ui.vaccinations') }}</h2>
                </div>
                <div class="medical-compact-list">
                    @forelse ($vaccinations as $vaccination)
                        <article>
                            <h3 class="font-bold">{{ $vaccination['name'] }}</h3>
                            <p class="mt-1 text-sm text-paw-muted">{{ __('presentation.given_on_next', ['given' => $vaccination['administered_on'] ?: __('ui.not_recorded'), 'next' => $vaccination['next_due_on'] ?: __('ui.not_set')]) }}</p>
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
                    <h2 id="shared-weight-title" class="text-xl font-bold">{{ __('ui.weight_history') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="medical-table min-w-[34rem]">
                        <thead><tr><th>{{ __('ui.date') }}</th><th>{{ __('ui.weight') }}</th><th>{{ __('ui.source') }}</th></tr></thead>
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
                        <h2 id="shared-documents-title" class="text-xl font-bold">{{ __('ui.documents') }}</h2>
                        <p class="mt-1 text-sm text-paw-muted">{{ $grant['allow_download'] ? __('ui.downloads_are_enabled_for_this_link') : __('ui.this_link_is_view_only') }}</p>
                    </div>
                </div>
                <x-medical-document-list :documents="$documents" />
            </section>
        @endif

        @if ($reminders !== [])
            <section class="medical-section" aria-labelledby="shared-reminders-title">
                <div class="medical-section__heading"><h2 id="shared-reminders-title" class="text-xl font-bold">{{ __('ui.upcoming_reminders') }}</h2></div>
                <div class="medical-compact-list">
                    @forelse ($reminders as $reminder)
                        <article><h3 class="font-bold">{{ $reminder['title'] }}</h3><p class="mt-1 text-sm text-paw-muted">{{ $reminder['due_at'] }} · {{ $reminder['priority'] }}</p></article>
                    @empty
                    @endforelse
                </div>
            </section>
        @endif

        <footer class="medical-privacy-strip">
            <x-ui-icon name="shield-check" size="lg" />
            <div>
                <strong>{{ __('ui.access_is_limited_to_the_sections_above') }}</strong>
                <span>{{ __('ui.the_owner_can_revoke_this_link_at_any_time_every_opening_and_permitted_download_is_recorded') }}</span>
            </div>
        </footer>
    </div>
</x-app-shell>
