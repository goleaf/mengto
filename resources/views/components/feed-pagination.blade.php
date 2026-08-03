@props(['feed'])

<div class="feed-pagination">
    <p>{{ __('presentation.feed_progress', ['shown' => $feed['showing'], 'total' => $feed['total']]) }}</p>

    @if ($feed['next_url'])
        <x-action-control
            :href="$feed['next_url']"
            label="{{ __('ui.load_more_ac8991ef01') }}"
            icon="chevron-down"
            variant="paper"
            size="regular"
        />
    @elseif ($feed['total'] > 0)
        <span class="feed-pagination__end">
            <x-ui-icon name="check" size="sm" />
            {{ __('ui.you_are_all_caught_up_fbfc240394') }}
        </span>
    @endif
</div>
