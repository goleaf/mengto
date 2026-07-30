@props(['feed'])

<section data-section="feed">
    <div class="feed-header">
        <x-ui.section-heading
            :eyebrow="$feed['summary']['eyebrow']"
            :title="$feed['summary']['title']"
            level="1"
            size="feed"
        />

        <x-ui.action-group>
            @if ($feed['sort'] !== 'latest')
                <x-ui.action-control
                    label="New posts"
                    icon="refresh-cw"
                    :href="$feed['new_posts_url']"
                    variant="paper"
                    size="regular"
                />
            @endif
            <x-ui.action-control
                label="New post"
                icon="plus"
                :href="$feed['composer_url']"
                size="regular"
            />
        </x-ui.action-group>
    </div>

    <p class="feed-header__description">{{ $feed['summary']['description'] }}</p>

    <x-feature.story-rail :stories="$feed['stories']" />
    <x-feature.feed-toolbar :feed="$feed" />
    <x-feature.quick-composer :href="$feed['composer_url']" :draft-count="$feed['draft_count']" />

    <div role="list" class="mt-5 grid gap-4">
        @forelse ($feed['posts'] as $post)
            <x-feature.feed-card :post="$post" :eager="$loop->first" />
        @empty
            <x-ui.empty-state
                icon="newspaper"
                title="No publications match these filters"
                compact
                role="listitem"
            />
        @endforelse
    </div>

    <x-feature.feed-pagination :feed="$feed" />
</section>
