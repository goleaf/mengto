@props(['event', 'href' => null])

<article class="event-row">
    <time datetime="{{ $event['datetime'] }}" class="event-row__date">
        <span>{{ $event['month'] }}</span>
        <strong>{{ $event['day'] }}</strong>
    </time>
    <div class="event-row__content">
        <x-status-badge :label="$event['status']" tone="surface" />
        <h3>{{ $event['title'] }}</h3>
        <p>{{ $event['place'] }}</p>
        <div class="event-row__meta">
            <x-icon-text icon="shield-check">{{ $event['access'] }}</x-icon-text>
            <x-icon-text icon="users">{{ $event['attendees'] }}</x-icon-text>
        </div>
    </div>
    @if ($href)
        <x-action-control
            label="{{ __('groups.detail.event.view') }}"
            icon="arrow-up-right"
            variant="paper"
            size="compact"
            :href="$href"
        />
    @endif
</article>
