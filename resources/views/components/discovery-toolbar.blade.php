@props(['categories', 'query', 'activeCategory', 'hiddenPreferenceCount' => 0])

<section data-section="discover-toolbar" aria-label="{{ __('discovery.search.region_label') }}" class="panel panel--padded-sm">
    <form method="GET" action="{{ route('discover.index') }}" class="discovery-toolbar">
        <input type="hidden" name="category" value="{{ $activeCategory }}">

        <x-search-field
            id="discover-search"
            label="{{ __('discovery.search.label') }}"
            placeholder="{{ __('discovery.search.placeholder') }}"
            :value="$query"
        />

        <div class="discovery-toolbar__categories" role="group" aria-label="{{ __('discovery.search.category_label') }}">
            @foreach ($categories as $category)
                <a
                    href="{{ $category['url'] }}"
                    @if ($category['active']) aria-current="page" @endif
                    @class([
                        'filter-chip',
                        'filter-chip--toolbar',
                        'discovery-toolbar__category-hidden' => $category['hidden'],
                    ])
                >
                    @if ($category['active'])
                        <x-lucide-check class="icon icon--sm" aria-hidden="true" />
                    @endif
                    <span>{{ $category['label'] }}</span>
                </a>
            @endforeach
        </div>

        <x-action-control
            type="submit"
            label="{{ __('discovery.actions.search') }}"
            icon="search"
            variant="primary"
            size="toolbar"
        />
    </form>

    @if ($hiddenPreferenceCount > 0)
        <div class="discovery-toolbar__preferences">
            <x-icon-text icon="eye-off">
                {{ trans_choice('discovery.preferences.hidden_count', $hiddenPreferenceCount, ['count' => $hiddenPreferenceCount]) }}
            </x-icon-text>
            <x-action-control
                endpoint="{{ route('discover.preferences.store') }}"
                label="{{ __('discovery.actions.reset_preferences') }}"
                icon="rotate-ccw"
                variant="quiet"
                size="compact"
                :payload="[
                    'action' => 'reset',
                    'return_category' => $activeCategory,
                    'return_q' => $query,
                ]"
            />
        </div>
    @endif
</section>
