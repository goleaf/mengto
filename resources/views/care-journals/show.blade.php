<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="care-journal-header">
            <div class="care-journal-header__identity">
                @if ($care_journal['image_url'])
                    <img src="{{ $care_journal['image_url'] }}" alt="{{ $care_journal['pet_name'] }}">
                @else
                    <span><x-lucide-paw-print class="size-8" aria-hidden="true" /></span>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('care-journals.index') }}" class="text-sm font-bold text-paw-leaf">Care journals</a>
                        <span class="text-paw-line">/</span>
                        <x-status-badge label="Private" icon="lock-keyhole" tone="surface" />
                    </div>
                    <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $care_journal['pet_name'] }}</h1>
                    <p class="mt-2 text-paw-muted">
                        {{ $care_journal['species'] }} · {{ $care_journal['breed'] }} · responsible: {{ $care_journal['current_caregiver'] }}
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-action-control label="Care report" icon="printer" :href="$care_journal['report_url']" />
                <x-action-control label="Plan & share" icon="list-checks" variant="primary" :href="$care_journal['manage_url']" />
            </div>
        </header>

        @if ($errors->any())
            <div class="care-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>The care action was not saved</strong>
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

        <x-care-today-summary :summary="$today_summary" />

        <div class="care-dashboard">
            <div class="grid min-w-0 content-start gap-5">
                <section class="care-section" aria-labelledby="quick-entry-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Quick record</p>
                            <h2 id="quick-entry-title" class="mt-1 text-xl font-bold">Add care action</h2>
                        </div>
                        <x-status-badge label="Private entry" icon="lock-keyhole" tone="surface" />
                    </div>
                    <x-care-entry-form
                        :action="route('care-journals.entries.store', $care_journal['slug'])"
                        :types="$entry_types"
                        :idempotency-key="$entry_idempotency_key"
                        :started-at="$form_defaults['started_at']"
                    />
                </section>

                <section class="care-section" aria-labelledby="timeline-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Recorded facts</p>
                            <h2 id="timeline-title" class="mt-1 text-xl font-bold">Recent care timeline</h2>
                        </div>
                        <span class="text-sm font-semibold text-paw-muted">{{ count($entries) }} entries</span>
                    </div>
                    <x-care-timeline :entries="$entries" />
                </section>

                <section class="care-section" aria-labelledby="weekly-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Seven-day view</p>
                            <h2 id="weekly-title" class="mt-1 text-xl font-bold">Care patterns</h2>
                        </div>
                    </div>
                    <p class="care-section-note">Bars show recorded walk and activity minutes. No entry means not recorded, not automatically missed.</p>
                    <x-care-weekly-table :days="$weekly" />
                </section>
            </div>

            <aside class="grid min-w-0 content-start gap-5">
                <section class="care-section" aria-labelledby="tasks-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Household handoff</p>
                            <h2 id="tasks-title" class="mt-1 text-xl font-bold">Open tasks</h2>
                        </div>
                        <a href="{{ $care_journal['manage_url'] }}#tasks" class="text-sm font-bold text-paw-leaf">Manage</a>
                    </div>
                    <x-care-task-list :tasks="$tasks" :journal-slug="$care_journal['slug']" />
                </section>

                <section class="care-section" aria-labelledby="medication-source-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Single source of truth</p>
                            <h2 id="medication-source-title" class="mt-1 text-xl font-bold">Medication today</h2>
                        </div>
                        <a href="{{ $medical['record_url'] }}" class="text-sm font-bold text-paw-leaf">Health record</a>
                    </div>
                    <div class="care-source-notice">
                        <x-lucide-pill class="size-5" aria-hidden="true" />
                        <p>Doses are recorded in the medical record and reflected here. The care journal never creates a second dose history.</p>
                    </div>
                    <div class="care-compact-list">
                        @forelse ($medical['active'] as $medication)
                            <article>
                                <div>
                                    <h3 class="font-bold">{{ $medication['name'] }}</h3>
                                    <p class="mt-1 text-sm text-paw-muted">{{ $medication['dose'] }} · {{ $medication['schedule'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-paw-muted">
                                        Next {{ $medication['next_dose'] ?: 'as instructed' }}
                                        @if ($medication['latest_status'])
                                            · latest {{ $medication['latest_status'] }} by {{ $medication['latest_by'] }}
                                        @endif
                                    </p>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ $medical['record_exists'] ? 'No active medication courses.' : 'Create or open a health record to manage medication safely.' }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="care-section" aria-labelledby="routines-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Current plan</p>
                            <h2 id="routines-title" class="mt-1 text-xl font-bold">Active routines</h2>
                        </div>
                    </div>
                    <div class="care-compact-list">
                        @forelse ($routines as $routine)
                            <article>
                                <span class="care-list-icon"><x-lucide-repeat-2 class="size-4" aria-hidden="true" /></span>
                                <div>
                                    <h3 class="font-bold">{{ $routine['name'] }}</h3>
                                    <p class="mt-1 text-sm text-paw-muted">{{ $routine['period'] }} · {{ $routine['start_time'] ?: 'Flexible time' }}</p>
                                    @if ($routine['instructions'])
                                        <p class="mt-1 text-xs leading-5 text-paw-muted">{{ $routine['instructions'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No routine templates yet.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
