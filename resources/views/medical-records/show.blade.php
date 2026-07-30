<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="medical-record-header">
            <div class="medical-record-header__identity">
                @if ($medical_record['image_url'])
                    <img src="{{ $medical_record['image_url'] }}" alt="{{ $medical_record['pet_name'] }}">
                @else
                    <span><x-lucide-paw-print class="size-8" aria-hidden="true" /></span>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('medical-records.index') }}" class="text-sm font-bold text-paw-leaf">{{ __('ui.health_records_bd13778e4d') }}</a>
                        <span class="text-paw-line">/</span>
                        <x-status-badge :label="$medical_record['privacy']" icon="lock-keyhole" tone="surface" />
                    </div>
                    <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $medical_record['pet_name'] }}</h1>
                    <p class="mt-2 text-paw-muted">
                        {{ $medical_record['species'] }} · {{ $medical_record['breed'] }} · {{ $medical_record['age'] }}
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-action-control label="{{ __('ui.emergency_card_4469bba9f9') }}" icon="siren" :href="$medical_record['emergency_url']" />
                <x-action-control label="{{ __('ui.add_health_entry_b157b1e3a1') }}" icon="plus" variant="primary" :href="$medical_record['manage_url']" />
            </div>
        </header>

        <x-medical-critical-summary :record="$medical_record" />

        <section class="medical-summary-grid" aria-label="{{ __('ui.health_record_summary_2b62898fb8') }}">
            @forelse ([
                ['label' => __('ui.current_weight_8a05fab730'), 'value' => $medical_record['current_weight'], 'icon' => 'scale'],
                ['label' => __('ui.last_visit_503f96395e'), 'value' => $medical_record['last_visit'], 'icon' => 'stethoscope'],
                ['label' => __('ui.next_appointment_c85b219f31'), 'value' => $medical_record['next_appointment'] ?: __('ui.not_scheduled_b3e24789bf'), 'icon' => 'calendar-clock'],
                ['label' => __('ui.microchip_230fe79bc1'), 'value' => $medical_record['microchip_masked'] ?: $medical_record['microchip_status'], 'icon' => 'scan-line'],
            ] as $summary)
                <div>
                    <x-dynamic-component :component="'lucide-'.$summary['icon']" class="size-5 text-paw-leaf" aria-hidden="true" />
                    <span>{{ $summary['label'] }}</span>
                    <strong>{{ $summary['value'] }}</strong>
                </div>
            @empty
                <p>{{ __('ui.no_summary_is_available_ac7819aaf0') }}</p>
            @endforelse
        </section>

        <div class="medical-dashboard">
            <div class="grid min-w-0 content-start gap-8">
                <section class="medical-section" aria-labelledby="medication-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.today_2b065c7c9c') }}</p>
                            <h2 id="medication-title" class="mt-1 text-xl font-bold">{{ __('ui.medication_schedule_120e5548af') }}</h2>
                        </div>
                        <span class="text-sm font-semibold text-paw-muted">{{ trans_choice('presentation.courses_count', count($medications), ['count' => count($medications)]) }}</span>
                    </div>

                    <div class="medical-medication-list">
                        @forelse ($medications as $medication)
                            <article class="medical-medication">
                                <div class="medical-medication__heading">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-bold">{{ $medication['name'] }}</h3>
                                            <x-status-badge :label="$medication['status_label']" :icon="$medication['can_record_dose'] ? 'circle-play' : 'circle-stop'" :tone="$medication['can_record_dose'] ? 'success' : 'surface'" />
                                            @if ($medication['is_high_risk'])
                                                <x-status-badge label="{{ __('ui.high_risk_protocol_380f517fc2') }}" icon="shield-alert" tone="warning" />
                                            @endif
                                        </div>
                                        <p class="mt-1 text-sm text-paw-muted">{{ $medication['dose'] }} · {{ $medication['route'] }} · {{ $medication['schedule'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-xs font-semibold text-paw-muted">{{ __('ui.next_dose_fa151dd45c') }}</span>
                                        <strong class="text-sm">{{ $medication['next_dose_at'] ?: __('ui.as_instructed_d5df7cf601') }}</strong>
                                    </div>
                                </div>

                                @if ($medication['latest_dose'])
                                    <div class="medical-dose-state">
                                        <x-lucide-user-round-check class="size-4" aria-hidden="true" />
                                        <span>
                                            {{ __('presentation.administered_by', ['status' => $medication['latest_dose']['status'], 'name' => $medication['latest_dose']['administered_by'], 'date' => $medication['latest_dose']['scheduled_for']]) }}
                                        </span>
                                    </div>
                                @endif

                                @if ($medication['instructions'])
                                    <p class="mt-3 text-sm leading-6 text-paw-muted">{{ $medication['instructions'] }}</p>
                                @endif

                                @if ($medication['can_record_dose'])
                                    <form method="POST" action="{{ route('medical-records.doses.store', $medical_record['slug']) }}" class="medical-dose-form">
                                        @csrf
                                        <input type="hidden" name="medication_id" value="{{ $medication['id'] }}">
                                        <input type="hidden" name="idempotency_key" value="{{ $medication['dose_idempotency_key'] }}">
                                        <input type="hidden" name="scheduled_for" value="{{ $medication['next_dose_value'] ?: now()->startOfMinute()->format('Y-m-d H:i:s') }}">
                                        <label>
                                            {{ __('ui.outcome_4e80abb5b1') }}
                                            <select name="status">
                                                <option value="given">{{ __('ui.given_0cf2f0e2cc') }}</option>
                                                <option value="partial">{{ __('ui.partially_given_ea7a033944') }}</option>
                                                <option value="refused">{{ __('ui.pet_refused_323b25a990') }}</option>
                                                <option value="vomited">{{ __('ui.vomited_after_dose_72c2263d13') }}</option>
                                                <option value="missed">{{ __('ui.missed_3d86eb082e') }}</option>
                                                <option value="late">{{ __('ui.given_late_b36ff42fba') }}</option>
                                            </select>
                                        </label>
                                        <label>
                                            {{ __('ui.actual_dose_2bc90570b5') }}
                                            <input name="dose_given" value="{{ $medication['dose'] }}" maxlength="120">
                                        </label>
                                        <button type="submit" class="action action--primary action--compact">
                                            <x-lucide-check class="icon icon--sm" aria-hidden="true" />
                                            <span>{{ __('ui.record_bfdd510698') }}</span>
                                        </button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <div class="medical-empty">
                                <x-lucide-pill class="size-7" aria-hidden="true" />
                                <p>{{ __('ui.no_medication_courses_recorded_d131d07a43') }}</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <x-medical-weight-chart :chart="$weight_chart" :weights="$weights" />
                <x-medical-timeline :events="$events" />
            </div>

            <aside class="grid min-w-0 content-start gap-8">
                <section class="medical-section" aria-labelledby="vaccination-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.preventive_care_e964c75227') }}</p>
                            <h2 id="vaccination-title" class="mt-1 text-xl font-bold">{{ __('ui.vaccinations_ed3861e631') }}</h2>
                        </div>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($vaccinations as $vaccination)
                            <article>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-bold">{{ $vaccination['name'] }}</h3>
                                    <x-status-badge :label="$vaccination['status_label']" :icon="$vaccination['status'] === 'overdue' ? 'triangle-alert' : 'syringe'" :tone="$vaccination['status'] === 'overdue' ? 'warning' : 'surface'" />
                                </div>
                                <p class="mt-2 text-sm text-paw-muted">{{ __('presentation.given_on', ['date' => $vaccination['administered_on'] ?: __('ui.not_recorded_b37c7879f6')]) }}</p>
                                <p class="mt-1 text-sm font-semibold">{{ __('presentation.next_on', ['date' => $vaccination['next_due_on'] ?: __('ui.confirm_with_clinic_9c031ce51e')]) }}</p>
                                <p class="mt-1 text-xs text-paw-muted">{{ $vaccination['verification'] }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_vaccination_history_3b5c264ff4') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="medical-section" aria-labelledby="reminder-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.care_calendar_02f69ec0b2') }}</p>
                            <h2 id="reminder-title" class="mt-1 text-xl font-bold">{{ __('ui.upcoming_5f1a2542e4') }}</h2>
                        </div>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($reminders as $reminder)
                            <article class="flex gap-3">
                                <span class="medical-list-icon"><x-lucide-bell-ring class="size-4" aria-hidden="true" /></span>
                                <div>
                                    <h3 class="font-bold">{{ $reminder['title'] }}</h3>
                                    <p class="mt-1 text-sm text-paw-muted">{{ $reminder['due_at'] }} · {{ $reminder['priority'] }}</p>
                                    @if ($reminder['instructions'])
                                        <p class="mt-1 text-xs leading-5 text-paw-muted">{{ $reminder['instructions'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_upcoming_reminders_cff2fdc762') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="medical-section" aria-labelledby="document-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.private_files_e57a38b69f') }}</p>
                            <h2 id="document-title" class="mt-1 text-xl font-bold">{{ __('ui.documents_b4e929d8bc') }}</h2>
                        </div>
                    </div>
                    <x-medical-document-list :documents="$documents" />
                </section>

                <section class="medical-section" aria-labelledby="access-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.least_access_9d3972d7e5') }}</p>
                            <h2 id="access-title" class="mt-1 text-xl font-bold">{{ __('ui.shared_access_e0277e0b5a') }}</h2>
                        </div>
                        <a href="{{ $medical_record['manage_url'] }}#access" class="text-sm font-bold text-paw-leaf">{{ __('ui.manage_5a23444828') }}</a>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($access_grants as $grant)
                            <article>
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="font-bold">{{ $grant['recipient_name'] }}</h3>
                                        <p class="mt-1 text-xs text-paw-muted">{{ __('presentation.role_views', ['role' => $grant['recipient_role'], 'views' => $grant['views']]) }}</p>
                                    </div>
                                    <x-status-badge :label="$grant['status']" :icon="$grant['active'] ? 'link' : 'link-2-off'" :tone="$grant['active'] ? 'success' : 'surface'" />
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_one_else_has_access_dc515852d6') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
