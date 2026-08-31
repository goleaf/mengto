@props(['feed', 'owner'])

<section data-section="feed">
    <x-story-rail :stories="$feed['stories']" />
    <x-feed-toolbar :feed="$feed" />
    <x-quick-composer :href="$feed['composer_url']" :owner="$owner" :draft-count="$feed['draft_count']" />

    <div role="list" class="mt-5 grid gap-4">
        @forelse ($feed['posts'] as $post)
            <x-feed-card :post="$post" :eager="$loop->first" />
        @empty
            <x-empty-state
                icon="newspaper"
                title="{{ __('ui.no_publications_match_these_filters') }}"
                compact
                role="listitem"
            />
        @endforelse
    </div>

    <x-feed-pagination :feed="$feed" />
</section>
