<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-6xl gap-7 care-report">
        <header class="care-report__header">
            <div>
                <a href="{{ $care_journal['show_url'] }}" class="care-report__back">
                    <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                    Return to journal
                </a>
                <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">Private care report</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $care_journal['pet_name'] }}</h1>
                <p class="mt-2 text-paw-muted">{{ $period }} · {{ $care_journal['timezone'] }}</p>
            </div>
            <button type="button" class="action action--primary" onclick="window.print()">
                <x-lucide-printer class="icon" aria-hidden="true" />
                <span>Print or save PDF</span>
            </button>
        </header>

        <section class="care-access-scope">
            <x-lucide-info class="size-5" aria-hidden="true" />
            <p>{{ $source_note }}</p>
        </section>

        <section class="care-section" aria-labelledby="report-summary-title">
            <div class="care-section__heading">
                <div>
                    <p class="text-xs font-bold uppercase text-paw-leaf">Recorded routine</p>
                    <h2 id="report-summary-title" class="mt-1 text-xl font-bold">Period table</h2>
                </div>
            </div>
            <x-care-weekly-table :days="$weekly" />
        </section>

        <section class="care-section" aria-labelledby="report-timeline-title">
            <div class="care-section__heading">
                <div>
                    <p class="text-xs font-bold uppercase text-paw-leaf">Source-preserving history</p>
                    <h2 id="report-timeline-title" class="mt-1 text-xl font-bold">Detailed timeline</h2>
                </div>
            </div>
            <x-care-timeline :entries="$entries" />
        </section>
    </div>
</x-app-shell>
