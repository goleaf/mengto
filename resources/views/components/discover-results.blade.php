@props(['results'])

<section data-section="discover-results" aria-labelledby="discover-results-title">
    <div class="flex items-end justify-between gap-4">
        <x-section-heading
            eyebrow="{{ __('ui.best_nearby_7b5eaf3d5e') }}"
            title="{{ __('ui.top_matches_ca85a90f5c') }}"
            title-id="discover-results-title"
            size="directory"
        />
        <p class="text-xs font-semibold text-paw-muted">{{ __('ui.sorted_for_mia_scout_8854e534fa') }}</p>
    </div>

    <div role="list" class="mt-4 grid gap-4">
        @forelse ($results as $result)
            <x-discover-result :result="$result" :eager="$loop->first" />
        @empty
            <x-empty-state
                icon="search-x"
                title="{{ __('ui.no_nearby_matches_17f4eead00') }}"
                role="listitem"
                description="{{ __('ui.try_a_broader_search_or_another_category_ac9e759879') }}"
                :href="route('discover.index')"
            />
        @endforelse
    </div>
</section>
