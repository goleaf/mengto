@props(['feed'])

<section data-section="feed">
    <div class="feed-header">
        <x-section-heading
            :eyebrow="$feed['summary']['eyebrow']"
            :title="$feed['summary']['title']"
            level="1"
            size="feed"
        />

        <x-action-group>
            @if ($feed['sort'] !== 'latest')
                <x-action-control
                    label="{{ __('ui.new_posts_35b75561fc') }}"
                    icon="refresh-cw"
                    :href="$feed['new_posts_url']"
                    variant="paper"
                    size="regular"
                />
            @endif
            <x-action-control
                label="{{ __('ui.new_post_7e50e2667b') }}"
                icon="plus"
                :href="$feed['composer_url']"
                size="regular"
            />
        </x-action-group>
    </div>

    <p class="feed-header__description">{{ $feed['summary']['description'] }}</p>

    <x-story-rail :stories="$feed['stories']" />
    <x-feed-toolbar :feed="$feed" />
    <x-quick-composer :href="$feed['composer_url']" :draft-count="$feed['draft_count']" />

    <div role="list" class="mt-5 grid gap-4">
        @forelse ($feed['posts'] as $post)
            <x-feed-card :post="$post" :eager="$loop->first" />
        @empty
            <x-empty-state
                icon="newspaper"
                title="{{ __('ui.no_publications_match_these_filters_3ccadd0d18') }}"
                compact
                role="listitem"
            />
        @endforelse
    </div>

    <x-feed-pagination :feed="$feed" />
</section>
