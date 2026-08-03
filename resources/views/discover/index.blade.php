<x-app-shell :owner="$owner" title="{{ __('discovery.page.browser_title') }}" active-section="discover">
    <x-page-stack class="discovery-page">
        <x-page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            heading-id="discover-heading"
            :count="$summary['count']"
            data-section="discover-header"
        />

        <x-discovery-category-nav
            :categories="$categories"
            :query="$query"
            :active-category="$activeCategory"
        />

        <x-discovery-toolbar
            :categories="$categories"
            :query="$query"
            :active-category="$activeCategory"
            :hidden-preference-count="$hiddenPreferenceCount"
        />

        @if ($activeCategoryHidden)
            <x-notice
                section="discover-hidden-category"
                icon="eye-off"
                title="{{ __('discovery.hidden_category.title') }}"
                description="{{ __('discovery.hidden_category.description') }}"
            >
                <x-slot:actions>
                    <x-action-control
                        endpoint="{{ route('discover.preferences.store') }}"
                        label="{{ __('discovery.actions.reset_preferences') }}"
                        icon="rotate-ccw"
                        variant="surface"
                        size="regular"
                        :payload="[
                            'action' => 'reset',
                            'return_category' => $activeCategory,
                            'return_q' => $query,
                        ]"
                    />
                </x-slot:actions>
            </x-notice>
        @elseif ($resultCount === 0)
            <x-empty-state
                icon="search-x"
                title="{{ __('discovery.empty.title') }}"
                description="{{ __('discovery.empty.description') }}"
                :href="route('discover.index')"
            />
        @else
            <div class="grid gap-8" data-section="discover-results">
                @foreach ($sections as $section)
                    <x-discovery-section
                        :section="$section"
                        :query="$query"
                        :active-category="$activeCategory"
                    />
                @endforeach
            </div>
        @endif
    </x-page-stack>
</x-app-shell>
