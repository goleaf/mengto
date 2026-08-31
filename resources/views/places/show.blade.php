<x-app-shell
    :title="$page_title"
    :active-section="$active_section"
>
    <x-page-stack>
        <x-text-link :href="route('places.index')" icon="arrow-left" variant="back">
            {{ __('ui.back_to_places') }}
        </x-text-link>

        <x-place-hero :place="$place" />

        <x-tab-list
            :tabs="$tabs"
            :label="__('presentation.sections_for', ['name' => $place['name']])"
            class="place-tabs"
        />

        <x-place-dashboard
            :place="$place"
            :active-tab="$active_tab"
            :content="$content"
            :check-in="$check_in"
            :collections="$collections"
            :claims="$claims"
            :corrections="$corrections"
            :can-manage="$can_manage"
            :report-url="$report_url"
            :correction-url="$correction_url"
            :warning-url="$warning_url"
            :review-url="$review_url"
            :question-url="$question_url"
            :claim-url="$claim_url"
            :event-url="$event_url"
            :pet-options="$pet_options"
            :default-pet-key="$default_pet_key"
        />
    </x-page-stack>
</x-app-shell>
