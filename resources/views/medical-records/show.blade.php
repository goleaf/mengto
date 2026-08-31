<x-app-shell :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="medical-record-header" data-medical-record-workspace-identity>
            <div class="medical-record-header__identity">
                @if ($medical_record['image_url'])
                    <img src="{{ $medical_record['image_url'] }}" alt="{{ $medical_record['pet_name'] }}" width="1200" height="900">
                @else
                    <span><x-ui-icon name="paw-print" size="2xl" /></span>
                @endif
                <div>
                    <x-detail-navigation
                        :href="route('medical-records.index')"
                        :label="__('ui.health_records')"
                    >
                        <span class="text-paw-line">/</span>
                        <x-status-badge :label="$medical_record['privacy']" icon="lock-keyhole" tone="surface" />
                    </x-detail-navigation>
                    <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $medical_record['pet_name'] }}</h1>
                    <p class="mt-2 text-paw-muted">
                        {{ $medical_record['species'] }} · {{ $medical_record['breed'] }} · {{ $medical_record['age'] }}
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-action-control label="{{ __('ui.emergency_card') }}" icon="siren" :href="$medical_record['emergency_url']" />
                <x-action-control label="{{ __('ui.add_health_entry') }}" icon="plus" variant="primary" :href="$medical_record['manage_url']" />
            </div>
        </header>

        <x-medical-critical-summary :record="$medical_record" />

        <section class="medical-summary-grid" aria-label="{{ __('ui.health_record_summary') }}">
            @forelse ([
                ['label' => __('ui.current_weight'), 'value' => $medical_record['current_weight'], 'icon' => 'scale'],
                ['label' => __('ui.last_visit'), 'value' => $medical_record['last_visit'], 'icon' => 'stethoscope'],
                ['label' => __('ui.next_appointment'), 'value' => $medical_record['next_appointment'] ?: __('ui.not_scheduled'), 'icon' => 'calendar-clock'],
                ['label' => __('ui.microchip'), 'value' => $medical_record['microchip_masked'] ?: $medical_record['microchip_status'], 'icon' => 'scan-line'],
            ] as $summary)
                <div>
                    <x-ui-icon :name="$summary['icon']" size="lg" class="text-paw-leaf" />
                    <span>{{ $summary['label'] }}</span>
                    <strong>{{ $summary['value'] }}</strong>
                </div>
            @empty
                <p>{{ __('ui.no_summary_is_available') }}</p>
            @endforelse
        </section>

        <div class="medical-dashboard">
            <div class="grid min-w-0 content-start gap-8">
                <section class="medical-section" aria-labelledby="medication-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.today') }}</p>
                            <h2 id="medication-title" class="mt-1 text-xl font-bold">{{ __('ui.medication_schedule') }}</h2>
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
                                                <x-status-badge label="{{ __('ui.high_risk_protocol') }}" icon="shield-alert" tone="warning" />
                                            @endif
                                        </div>
                                        <p class="mt-1 text-sm text-paw-muted">{{ $medication['dose'] }} · {{ $medication['route'] }} · {{ $medication['schedule'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-xs font-semibold text-paw-muted">{{ __('ui.next_dose') }}</span>
                                        <strong class="text-sm">{{ $medication['next_dose_at'] ?: __('ui.as_instructed') }}</strong>
                                    </div>
                                </div>

                                @if ($medication['latest_dose'])
                                    <div class="medical-dose-state">
                                        <x-ui-icon name="user-round-check" size="sm" />
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
                                            {{ __('ui.outcome') }}
                                            <select name="status">
                                                <option value="given">{{ __('ui.given') }}</option>
                                                <option value="partial">{{ __('ui.partially_given') }}</option>
                                                <option value="refused">{{ __('ui.pet_refused') }}</option>
                                                <option value="vomited">{{ __('ui.vomited_after_dose') }}</option>
                                                <option value="missed">{{ __('ui.missed') }}</option>
                                                <option value="late">{{ __('ui.given_late') }}</option>
                                            </select>
                                        </label>
                                        <label>
                                            {{ __('ui.actual_dose') }}
                                            <input name="dose_given" value="{{ $medication['dose'] }}" maxlength="120">
                                        </label>
                                        <button type="submit" class="action action--primary action--compact">
                                            <x-ui-icon name="check" size="sm" />
                                            <span>{{ __('ui.record') }}</span>
                                        </button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <div class="medical-empty">
                                <x-ui-icon name="pill" size="xl" />
                                <p>{{ $medical_record['medication_knowledge_label'] }}</p>
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
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.preventive_care') }}</p>
                            <h2 id="vaccination-title" class="mt-1 text-xl font-bold">{{ __('ui.vaccinations') }}</h2>
                        </div>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($vaccinations as $vaccination)
                            <article>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-bold">{{ $vaccination['name'] }}</h3>
                                    <x-status-badge :label="$vaccination['status_label']" :icon="$vaccination['status'] === 'overdue' ? 'triangle-alert' : 'syringe'" :tone="$vaccination['status'] === 'overdue' ? 'warning' : 'surface'" />
                                </div>
                                <p class="mt-2 text-sm text-paw-muted">{{ __('presentation.given_on', ['date' => $vaccination['administered_on'] ?: __('ui.not_recorded')]) }}</p>
                                <p class="mt-1 text-sm font-semibold">{{ __('presentation.next_on', ['date' => $vaccination['next_due_on'] ?: __('ui.confirm_with_clinic')]) }}</p>
                                <p class="mt-1 text-xs text-paw-muted">{{ $vaccination['verification'] }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_vaccination_history') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="medical-section" aria-labelledby="reminder-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.care_calendar') }}</p>
                            <h2 id="reminder-title" class="mt-1 text-xl font-bold">{{ __('ui.upcoming') }}</h2>
                        </div>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($reminders as $reminder)
                            <article class="flex gap-3">
                                <span class="medical-list-icon"><x-ui-icon name="bell-ring" size="sm" /></span>
                                <div>
                                    <h3 class="font-bold">{{ $reminder['title'] }}</h3>
                                    <p class="mt-1 text-sm text-paw-muted">{{ $reminder['due_at'] }} · {{ $reminder['priority'] }}</p>
                                    @if ($reminder['instructions'])
                                        <p class="mt-1 text-xs leading-5 text-paw-muted">{{ $reminder['instructions'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_upcoming_reminders') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="medical-section" aria-labelledby="document-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.private_files') }}</p>
                            <h2 id="document-title" class="mt-1 text-xl font-bold">{{ __('ui.documents') }}</h2>
                        </div>
                    </div>
                    <x-medical-document-list :documents="$documents" />
                </section>

                <section class="medical-section" aria-labelledby="access-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.least_access') }}</p>
                            <h2 id="access-title" class="mt-1 text-xl font-bold">{{ __('ui.shared_access') }}</h2>
                        </div>
                        <a href="{{ $medical_record['manage_url'] }}#access" class="inline-flex items-center gap-1 text-sm font-bold text-paw-leaf">
                            <x-ui-icon name="settings-2" size="sm" />
                            <span>{{ __('ui.manage') }}</span>
                        </a>
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
                            <p class="text-sm text-paw-muted">{{ __('ui.no_one_else_has_access') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
