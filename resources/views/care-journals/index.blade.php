<x-app-shell :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <x-page-header
            :eyebrow="__('ui.private_family_workspace')"
            :title="__('ui.care_journals')"
            :description="__('ui.today_s_feeding_water_walks_rest_toilet_activity_routines_and_handoffs_for_every_pet_you_manage')"
            heading-id="care-journals-heading"
            :action-label="__('ui.create_journal')"
            action-icon="plus"
            :action-href="route('care-journals.create')"
            data-section="care-journals-header"
        />

        <section class="care-family-strip" aria-label="{{ __('ui.family_care_overview') }}">
            <x-ui-icon name="users-round" size="lg" />
            <div>
                <strong>{{ __('ui.one_place_for_the_household') }}</strong>
                <p>{{ __('ui.each_pet_keeps_a_separate_journal_missing_records_stay_marked_as_unknown_never_silently_treated_as_missed_care') }}</p>
            </div>
        </section>

        <section class="care-directory-grid" aria-label="{{ __('ui.your_pet_care_journals') }}">
            @forelse ($journals as $journal)
                <x-care-journal-card :journal="$journal" />
            @empty
                <div class="care-empty care-empty--wide">
                    <x-ui-icon name="notebook-tabs" size="2xl" />
                    <h2 class="text-xl font-bold">{{ __('ui.no_private_care_journals_yet') }}</h2>
                    <p>{{ __('ui.create_one_for_a_pet_you_manage_then_add_structured_daily_actions_and_family_tasks') }}</p>
                    <x-action-control :href="route('care-journals.create')" label="{{ __('ui.create_first_journal') }}" icon="plus" variant="primary" />
                </div>
            @endforelse
        </section>

        @if ($journals->hasPages())
            <div>{{ $journals->withQueryString()->links() }}</div>
        @endif
    </div>
</x-app-shell>
