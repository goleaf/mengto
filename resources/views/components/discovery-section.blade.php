@props(['section', 'query', 'activeCategory'])

<section class="discovery-section" data-discovery-section="{{ $section['category'] }}" aria-labelledby="discover-{{ $section['category'] }}-title">
    <div class="discovery-section__header">
        <div class="discovery-section__identity">
            <span class="discovery-section__icon" aria-hidden="true">
                <x-ui-icon :name="$section['icon']" />
            </span>
            <div class="min-w-0">
                <h2 id="discover-{{ $section['category'] }}-title" class="discovery-section__title">{{ $section['title'] }}</h2>
                <p class="discovery-section__description">{{ $section['description'] }}</p>
            </div>
        </div>

        <div class="discovery-section__actions">
            <x-action-control
                :href="$section['directory_url']"
                label="{{ __('discovery.actions.open_directory') }}"
                icon="arrow-up-right"
                variant="surface"
                size="compact"
            />
            <x-action-control
                endpoint="{{ route('discover.preferences.store') }}"
                label="{{ __('discovery.actions.hide_category') }}"
                icon="eye-off"
                variant="quiet"
                size="compact"
                :payload="[
                    'action' => 'hide',
                    'scope' => 'category',
                    'category' => $section['category'],
                    'reason_code' => 'not_interested',
                    'return_category' => $activeCategory,
                    'return_q' => $query,
                ]"
            />
        </div>
    </div>

    @if ($section['items'] === [])
        <x-empty-state
            icon="search-x"
            title="{{ __('discovery.empty.section_title') }}"
            description="{{ __('discovery.empty.section_description') }}"
            :href="$section['directory_url']"
        />
    @else
        <div role="list" class="discovery-results-grid">
            @foreach ($section['items'] as $item)
                <x-discovery-result-card
                    :item="$item"
                    :query="$query"
                    :active-category="$activeCategory"
                    :eager="$loop->first"
                />
            @endforeach
        </div>
    @endif
</section>
