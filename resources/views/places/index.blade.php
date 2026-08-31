<x-directory-page
    :title="$page_title"
    :active-section="$active_section"
    :summary="$summary"
    header-section="places-header"
    action-label="{{ __('place_directory.page.action') }}"
    action-icon="map-pin-plus"
    :action-href="$places['add_url']"
>
    <x-slot:summary-strip>
        <x-summary-strip
            :items="$summary['highlights']"
            label="{{ __('place_directory.page.summary_label') }}"
            :icons="['clock-3', 'paw-print', 'shield-check', 'layers-3']"
            empty="{{ __('place_directory.page.summary_unavailable') }}"
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
