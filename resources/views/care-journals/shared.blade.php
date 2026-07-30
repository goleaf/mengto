<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-6xl gap-7">
        <header class="care-shared-header">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge label="Temporary private access" icon="key-round" tone="success" size="regular" />
                    <x-status-badge :label="$grant['recipient_role']" icon="user-round-check" tone="surface" />
                </div>
                <h1 class="mt-4 text-3xl font-bold sm:text-4xl">{{ $care_journal['pet_name'] }} care plan</h1>
                <p class="mt-2 text-paw-muted">{{ $grant['label'] }} · expires {{ $grant['expires_at'] }}</p>
            </div>
            <div class="care-shared-header__pet">
                @if ($care_journal['image_url'])
                    <img src="{{ $care_journal['image_url'] }}" alt="{{ $care_journal['pet_name'] }}">
                @else
                    <x-lucide-paw-print class="size-6" aria-hidden="true" />
                @endif
            </div>
        </header>

        @if ($errors->any())
            <div class="care-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>The report was not saved</strong>
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

        <section class="care-access-scope" aria-label="Temporary access limits">
            <x-lucide-shield-check class="size-5" aria-hidden="true" />
            <div>
                <strong>Access is limited to: {{ implode(', ', $grant['sections']) }}</strong>
                <p>
                    {{ $grant['allow_add'] ? 'You may add structured reports.' : 'Read-only access.' }}
                    {{ $grant['allow_location'] ? 'Location sharing is enabled.' : 'Exact locations remain hidden.' }}
                    {{ $grant['allow_media'] ? 'Media is enabled.' : 'Private media remains hidden.' }}
                </p>
            </div>
        </section>

        <div class="care-dashboard">
            <div class="grid min-w-0 content-start gap-5">
                @if ($grant['allow_add'] && $entry_types)
                    <section class="care-section" aria-labelledby="shared-entry-title">
                        <div class="care-section__heading">
                            <div>
                                <p class="text-xs font-bold uppercase text-paw-leaf">Sitter or specialist report</p>
                                <h2 id="shared-entry-title" class="mt-1 text-xl font-bold">Record completed care</h2>
                            </div>
                        </div>
                        <x-care-entry-form
                            :action="route('care-access.entries.store', ['token' => $token])"
                            :types="$entry_types"
                            :idempotency-key="$entry_idempotency_key"
                            :started-at="$form_defaults['started_at']"
                            :source-type="$grant['recipient_role'] === 'Sitter' ? 'sitter' : 'specialist'"
                            :source-name="$grant['recipient_name']"
                            :allow-location="$grant['allow_location']"
                            :allow-media="$grant['allow_media']"
                            submit-label="Submit care report"
                            compact
                        />
                    </section>
                @endif

                <section class="care-section" aria-labelledby="shared-timeline-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Allowed history</p>
                            <h2 id="shared-timeline-title" class="mt-1 text-xl font-bold">Care timeline</h2>
                        </div>
                    </div>
                    <x-care-timeline :entries="$entries" />
                </section>
            </div>
            <aside class="grid min-w-0 content-start gap-5">
                <section class="care-section" aria-labelledby="shared-task-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Assigned plan</p>
                            <h2 id="shared-task-title" class="mt-1 text-xl font-bold">Open tasks</h2>
                        </div>
                    </div>
                    <x-care-task-list :tasks="$tasks" read-only />
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
