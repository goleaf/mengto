<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <x-page-header
            :eyebrow="__('ui.private_family_workspace_521e77339e')"
            :title="__('ui.care_journals_efcbb402a3')"
            :description="__('ui.today_s_feeding_water_walks_rest_toilet_activity_dc1cdec032')"
            heading-id="care-journals-heading"
            :action-label="__('ui.create_journal_0be6b9b3a5')"
            action-icon="plus"
            :action-href="route('care-journals.create')"
            data-section="care-journals-header"
        />

        <section class="care-family-strip" aria-label="{{ __('ui.family_care_overview_b363d25aa9') }}">
            <x-ui-icon name="users-round" size="lg" />
            <div>
                <strong>{{ __('ui.one_place_for_the_household_93084c6a2d') }}</strong>
                <p>{{ __('ui.each_pet_keeps_a_separate_journal_missing_records_e8f178c96b') }}</p>
            </div>
        </section>

        <section class="care-directory-grid" aria-label="{{ __('ui.your_pet_care_journals_2cf8149a95') }}">
            @forelse ($journals as $journal)
                <x-care-journal-card :journal="$journal" />
            @empty
                <div class="care-empty care-empty--wide">
                    <x-ui-icon name="notebook-tabs" size="2xl" />
                    <h2 class="text-xl font-bold">{{ __('ui.no_private_care_journals_yet_6b892c7c93') }}</h2>
                    <p>{{ __('ui.create_one_for_a_pet_you_manage_then_17f20e662e') }}</p>
                    <x-action-control :href="route('care-journals.create')" label="{{ __('ui.create_first_journal_be6634cd47') }}" icon="plus" variant="primary" />
                </div>
            @endforelse
        </section>

        @if ($journals->hasPages())
            <div>{{ $journals->withQueryString()->links() }}</div>
        @endif
    </div>
</x-app-shell>
