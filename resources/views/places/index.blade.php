<x-directory-page
    :owner="$owner"
    :title="$page_title"
    :active-section="$active_section"
    :summary="$summary"
    header-section="places-header"
    action-label="{{ __('ui.add_place_b37bea1398') }}"
    action-icon="map-pin-plus"
    :action-href="$places['add_url']"
>
    <x-slot:summary-strip>
        <x-summary-strip
            :items="$summary['highlights']"
            label="{{ __('ui.place_catalog_summary_f2b328299b') }}"
            :icons="['clock-3', 'paw-print', 'shield-check', 'layers-3']"
            empty="{{ __('ui.place_summary_unavailable_ceb1a84a1d') }}"
            :columns="4"
            data-section="places-summary"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
    </x-slot:toolbar>

    <x-slot:results>
        <x-place-directory :places="$places" />
    </x-slot:results>
</x-directory-page>
