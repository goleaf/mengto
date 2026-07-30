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
                        <a href="{{ route('medical-records.index') }}" class="text-sm font-bold text-paw-leaf">Health records</a>
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
                <x-action-control label="Emergency card" icon="siren" :href="$medical_record['emergency_url']" />
                <x-action-control label="Add health entry" icon="plus" variant="primary" :href="$medical_record['manage_url']" />
            </div>
        </header>

        <x-medical-critical-summary :record="$medical_record" />

        <section class="medical-summary-grid" aria-label="Health record summary">
            @forelse ([
                ['label' => 'Current weight', 'value' => $medical_record['current_weight'], 'icon' => 'scale'],
                ['label' => 'Last visit', 'value' => $medical_record['last_visit'], 'icon' => 'stethoscope'],
                ['label' => 'Next appointment', 'value' => $medical_record['next_appointment'] ?: 'Not scheduled', 'icon' => 'calendar-clock'],
                ['label' => 'Microchip', 'value' => $medical_record['microchip_masked'] ?: $medical_record['microchip_status'], 'icon' => 'scan-line'],
            ] as $summary)
                <div>
                    <x-dynamic-component :component="'lucide-'.$summary['icon']" class="size-5 text-paw-leaf" aria-hidden="true" />
                    <span>{{ $summary['label'] }}</span>
                    <strong>{{ $summary['value'] }}</strong>
                </div>
            @empty
                <p>No summary is available.</p>
            @endforelse
        </section>

        <div class="medical-dashboard">
            <div class="grid min-w-0 content-start gap-8">
                <section class="medical-section" aria-labelledby="medication-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Today</p>
                            <h2 id="medication-title" class="mt-1 text-xl font-bold">Medication schedule</h2>
                        </div>
                        <span class="text-sm font-semibold text-paw-muted">{{ count($medications) }} courses</span>
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
                                                <x-status-badge label="High-risk protocol" icon="shield-alert" tone="warning" />
                                            @endif
                                        </div>
                                        <p class="mt-1 text-sm text-paw-muted">{{ $medication['dose'] }} · {{ $medication['route'] }} · {{ $medication['schedule'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-xs font-semibold text-paw-muted">Next dose</span>
                                        <strong class="text-sm">{{ $medication['next_dose_at'] ?: 'As instructed' }}</strong>
                                    </div>
                                </div>

                                @if ($medication['latest_dose'])
                                    <div class="medical-dose-state">
                                        <x-lucide-user-round-check class="size-4" aria-hidden="true" />
                                        <span>
                                            {{ $medication['latest_dose']['status'] }}
                                            by {{ $medication['latest_dose']['administered_by'] }}
                                            · {{ $medication['latest_dose']['scheduled_for'] }}
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
                                            Outcome
                                            <select name="status">
                                                <option value="given">Given</option>
                                                <option value="partial">Partially given</option>
                                                <option value="refused">Pet refused</option>
                                                <option value="vomited">Vomited after dose</option>
                                                <option value="missed">Missed</option>
                                                <option value="late">Given late</option>
                                            </select>
                                        </label>
                                        <label>
                                            Actual dose
                                            <input name="dose_given" value="{{ $medication['dose'] }}" maxlength="120">
                                        </label>
                                        <button type="submit" class="action action--primary action--compact">
                                            <x-lucide-check class="icon icon--sm" aria-hidden="true" />
                                            <span>Record</span>
                                        </button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <div class="medical-empty">
                                <x-lucide-pill class="size-7" aria-hidden="true" />
                                <p>No medication courses recorded.</p>
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
                            <p class="text-xs font-bold uppercase text-paw-leaf">Preventive care</p>
                            <h2 id="vaccination-title" class="mt-1 text-xl font-bold">Vaccinations</h2>
                        </div>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($vaccinations as $vaccination)
                            <article>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-bold">{{ $vaccination['name'] }}</h3>
                                    <x-status-badge :label="$vaccination['status_label']" :icon="$vaccination['status'] === 'overdue' ? 'triangle-alert' : 'syringe'" :tone="$vaccination['status'] === 'overdue' ? 'warning' : 'surface'" />
                                </div>
                                <p class="mt-2 text-sm text-paw-muted">Given {{ $vaccination['administered_on'] ?: 'not recorded' }}</p>
                                <p class="mt-1 text-sm font-semibold">Next: {{ $vaccination['next_due_on'] ?: 'Confirm with clinic' }}</p>
                                <p class="mt-1 text-xs text-paw-muted">{{ $vaccination['verification'] }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No vaccination history.</p>
                        @endforelse
                    </div>
                </section>

                <section class="medical-section" aria-labelledby="reminder-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Care calendar</p>
                            <h2 id="reminder-title" class="mt-1 text-xl font-bold">Upcoming</h2>
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
                            <p class="text-sm text-paw-muted">No upcoming reminders.</p>
                        @endforelse
                    </div>
                </section>

                <section class="medical-section" aria-labelledby="document-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Private files</p>
                            <h2 id="document-title" class="mt-1 text-xl font-bold">Documents</h2>
                        </div>
                    </div>
                    <x-medical-document-list :documents="$documents" />
                </section>

                <section class="medical-section" aria-labelledby="access-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Least access</p>
                            <h2 id="access-title" class="mt-1 text-xl font-bold">Shared access</h2>
                        </div>
                        <a href="{{ $medical_record['manage_url'] }}#access" class="text-sm font-bold text-paw-leaf">Manage</a>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($access_grants as $grant)
                            <article>
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="font-bold">{{ $grant['recipient_name'] }}</h3>
                                        <p class="mt-1 text-xs text-paw-muted">{{ $grant['recipient_role'] }} · {{ $grant['views'] }} opens</p>
                                    </div>
                                    <x-status-badge :label="$grant['status']" :icon="$grant['active'] ? 'link' : 'link-2-off'" :tone="$grant['active'] ? 'success' : 'surface'" />
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No one else has access.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
