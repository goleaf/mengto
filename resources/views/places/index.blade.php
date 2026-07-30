<x-layout.directory-page
    :owner="$owner"
    :title="$page_title"
    :active-section="$active_section"
    :summary="$summary"
    header-section="places-header"
    action-label="Add place"
    action-icon="map-pin-plus"
    :action-href="$places['add_url']"
>
    <x-slot:summary-strip>
        <x-ui.summary-strip
            :items="$summary['highlights']"
            label="Place catalog summary"
            :icons="['clock-3', 'paw-print', 'shield-check', 'layers-3']"
            empty="Place summary unavailable."
            :columns="4"
            data-section="places-summary"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
    </x-slot:toolbar>

    <x-slot:results>
        <x-feature.place-directory :places="$places" />
    </x-slot:results>
</x-layout.directory-page>
