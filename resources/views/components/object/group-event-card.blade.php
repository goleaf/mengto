@props(['event', 'href' => null])

<article class="event-row">
    <time datetime="{{ $event['datetime'] }}" class="event-row__date">
        <span>{{ $event['month'] }}</span>
        <strong>{{ $event['day'] }}</strong>
    </time>
    <div class="event-row__content">
        <x-ui.status-badge :label="$event['status']" tone="surface" />
        <h3>{{ $event['title'] }}</h3>
        <p>{{ $event['place'] }}</p>
        <div class="event-row__meta">
            <x-ui.icon-text icon="shield-check">{{ $event['access'] }}</x-ui.icon-text>
            <x-ui.icon-text icon="users">{{ $event['attendees'] }}</x-ui.icon-text>
        </div>
    </div>
    @if ($href)
        <x-ui.action-control
            label="View event"
            icon="arrow-up-right"
            variant="paper"
            size="compact"
            :href="$href"
        />
    @endif
</article>
