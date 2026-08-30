<x-app-shell :owner="$owner" :title="$page_title" active-section="discover">
    <x-page-stack>
        <x-detail-navigation
            :href="route('discover.index', ['category' => 'owners'])"
            :label="__('member_profiles.actions.back_to_discovery')"
        />

        <x-page-header
            :eyebrow="__('member_profiles.page.eyebrow')"
            :title="$profile['name']"
            :description="$profile['description']"
            heading-id="member-profile-heading"
            :count="$profile['status']"
            data-section="member-profile-header"
        />

        <x-main-sidebar-layout>
            <x-slot:main>
                <x-collection-section
                    section="member-posts"
                    :eyebrow="__('member_profiles.posts.eyebrow')"
                    :title="__('member_profiles.posts.title')"
                    title-id="member-posts-heading"
                >
                    @forelse ($profile['posts'] as $publication)
                        <x-content-publication-card :publication="$publication" role="listitem" />
                    @empty
                        <x-empty-state
                            icon="newspaper"
                            :title="__('member_profiles.posts.empty_title')"
                            :description="__('member_profiles.posts.empty_description')"
                            compact
                            role="listitem"
                        />
                    @endforelse
                </x-collection-section>
            </x-slot:main>

            <x-slot:sidebar>
                <x-content-panel
                    section="member-public-details"
                    :eyebrow="__('member_profiles.details.eyebrow')"
                    :title="__('member_profiles.details.title')"
                    size="compact"
                >
                    <x-definition-list :items="$profile['details']" strong class="section-body" />
                </x-content-panel>

                <x-content-panel
                    section="member-public-pets"
                    :eyebrow="__('member_profiles.pets.eyebrow')"
                    :title="__('member_profiles.pets.title')"
                    size="compact"
                >
                    <ul class="section-body divide-y divide-paw-line" aria-label="{{ __('member_profiles.pets.title') }}">
                        @forelse ($profile['pets'] as $pet)
                            <li class="py-3 first:pt-0 last:pb-0">
                                <x-text-link :href="$pet['url']">{{ $pet['name'] }}</x-text-link>
                                <p class="mt-1 text-sm text-paw-muted">{{ $pet['description'] }}</p>
                            </li>
                        @empty
                            <li class="text-sm text-paw-muted">{{ __('member_profiles.pets.empty') }}</li>
                        @endforelse
                    </ul>
                </x-content-panel>
            </x-slot:sidebar>
        </x-main-sidebar-layout>
    </x-page-stack>
</x-app-shell>
