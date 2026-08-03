<x-app-shell :owner="$owner" title="{{ __('pet_workspace.browser_title') }}" active-section="pets">
    <x-page-stack data-section="pet-profile-workspace">
        <x-page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            heading-id="pet-workspace-heading"
            :count="$summary['count']"
            :action-label="__('pet_profiles.actions.create')"
            action-icon="plus"
            :action-href="route('pets.manage.create')"
            data-section="pet-workspace-header"
        >
            <x-slot:actions>
                <x-action-control
                    :label="__('pet_workspace.invitations')"
                    icon="inbox"
                    variant="surface"
                    size="regular"
                    :href="route('pets.manage.invitations')"
                />
            </x-slot:actions>
        </x-page-header>

        <nav
            class="flex min-w-0 flex-wrap items-center gap-2"
            aria-label="{{ __('pet_workspace.navigation') }}"
            data-section="pet-workspace-navigation"
        >
            <x-action-control
                :label="__('pet_workspace.discover_pets')"
                icon="compass"
                variant="paper"
                size="regular"
                :href="$discoverPetsUrl"
            />
            <x-action-control
                :label="__('pet_workspace.care_journals')"
                icon="clipboard-list"
                variant="paper"
                size="regular"
                :href="route('care-journals.index')"
            />
            <x-action-control
                :label="__('pet_workspace.health_records')"
                icon="stethoscope"
                variant="paper"
                size="regular"
                :href="route('medical-records.index')"
            />
        </nav>

        @if ($invitationCount > 0)
            <x-notice
                section="pet-workspace-invitations"
                icon="mail-check"
                :title="$invitationTitle"
                :description="__('pet_workspace.invitation_description')"
            >
                <x-slot:actions>
                    <x-action-control
                        :label="__('pet_workspace.review_invitations')"
                        icon="arrow-right"
                        variant="surface"
                        size="regular"
                        :href="route('pets.manage.invitations')"
                    />
                </x-slot:actions>
            </x-notice>
        @endif

        <x-directory-toolbar
            :filters="$filters"
            :label="__('pet_workspace.toolbar')"
            :filters-label="__('pet_workspace.filters_label')"
            :sort-label="__('pet_workspace.sort_label')"
            section="pet-workspace-filters"
            search-id="pet-workspace-search"
            :search-label="__('pet_workspace.search_label')"
            :search-placeholder="__('pet_workspace.search_placeholder')"
            :query="$query"
            :active-filter="$activeFilter"
            :active-sort="$activeSort"
            :sort-options="$sortOptions"
        />

        <section data-section="pet-workspace-results" aria-labelledby="pet-workspace-results-heading">
            <h2 id="pet-workspace-results-heading" class="sr-only">
                {{ __('pet_workspace.results') }}
            </h2>

            @if ($pets->isEmpty())
                <x-empty-state
                    :icon="$isFiltered ? 'search-x' : 'paw-print'"
                    :title="$isFiltered ? __('pet_workspace.filtered_empty_title') : __('pet_workspace.empty_title')"
                    :description="$isFiltered ? __('pet_workspace.filtered_empty_description') : __('pet_workspace.empty_description')"
                    :href="$isFiltered ? route('pets.index') : route('pets.manage.create')"
                    :action-label="$isFiltered ? __('pet_workspace.clear_filters') : __('pet_profiles.actions.create')"
                />
            @else
                <div role="list" class="grid gap-4 sm:grid-cols-2">
                    @foreach ($pets as $pet)
                        <x-pet-directory-card
                            :pet="$pet"
                            :eager="$loop->first"
                        />
                    @endforeach
                </div>

                @if ($pets->hasPages())
                    <nav class="mt-6" aria-label="{{ __('pet_workspace.pagination') }}">
                        {{ $pets->links('pagination::tailwind') }}
                    </nav>
                @endif
            @endif
        </section>
    </x-page-stack>
</x-app-shell>
