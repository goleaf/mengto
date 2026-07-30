@props(['feed'])

<div class="feed-pagination">
    <p>Showing {{ $feed['showing'] }} of {{ $feed['total'] }} publications</p>

    @if ($feed['next_url'])
        <x-ui.action-control
            :href="$feed['next_url']"
            label="Load more"
            icon="chevron-down"
            variant="paper"
            size="regular"
        />
    @elseif ($feed['total'] > 0)
        <span class="feed-pagination__end">
            <x-lucide-check class="icon icon--sm" aria-hidden="true" />
            You are all caught up
        </span>
    @endif
</div>
