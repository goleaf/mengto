<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-6xl gap-7 care-report">
        <header class="care-report__header">
            <div>
                <a href="{{ $care_journal['show_url'] }}" class="care-report__back">
                    <x-ui-icon name="arrow-left" size="sm" />
                    {{ __('ui.return_to_journal_371119d3a9') }}
                </a>
                <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">{{ __('ui.private_care_report_0322976bf9') }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $care_journal['pet_name'] }}</h1>
                <p class="mt-2 text-paw-muted">{{ $period }} · {{ $care_journal['timezone'] }}</p>
            </div>
            <button type="button" class="action action--primary" data-print-page>
                <x-ui-icon name="printer" />
                <span>{{ __('ui.print_or_save_pdf_a41f904b40') }}</span>
            </button>
        </header>

        <section class="care-access-scope">
            <x-ui-icon name="info" size="lg" />
            <p>{{ $source_note }}</p>
        </section>

        <section class="care-section" aria-labelledby="report-summary-title">
            <div class="care-section__heading">
                <div>
                    <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.recorded_routine_cd48ad919c') }}</p>
                    <h2 id="report-summary-title" class="mt-1 text-xl font-bold">{{ __('ui.period_table_0f4b2ccb98') }}</h2>
                </div>
            </div>
            <x-care-weekly-table :days="$weekly" />
        </section>

        <section class="care-section" aria-labelledby="report-timeline-title">
            <div class="care-section__heading">
                <div>
                    <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.source_preserving_history_1dc6f42d4a') }}</p>
                    <h2 id="report-timeline-title" class="mt-1 text-xl font-bold">{{ __('ui.detailed_timeline_0512c22686') }}</h2>
                </div>
            </div>
            <x-care-timeline :entries="$entries" />
        </section>
    </div>
</x-app-shell>
