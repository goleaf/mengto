<div class="place-directory">
    <x-place-search-panel :places="$places" :filters="$filters" />

    <x-place-directory-controls
        :mode-links="$modeLinks"
        :view-links="$viewLinks"
        :sort-parameters="$sortParameters"
        :sort-options="$places['sort_options']"
        :current-sort="$filters['sort']"
        :browse-url="$places['browse_url']"
    />

    <div class="place-directory__workspace place-directory__workspace--{{ $filters['view'] }}">
        @if ($filters['view'] !== 'list')
            <x-place-map
                :places="$places['map_items']"
                :selected="$places['selected']"
                :layer="$filters['layer']"
                :layer-label="$places['layer_options'][$filters['layer']]"
                :emergency="$places['emergency']"
            />
        @endif

        @if ($filters['view'] !== 'map' && $filters['view'] !== 'fullscreen')
            <x-place-results
                :places="$places"
                :selected-key="$places['selected']['key'] ?? null"
                :layer-links="$layerLinks"
            />
        @endif
    </div>

    <x-place-comparison :places="$places['comparison']" />
</div>
