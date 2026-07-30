@props(['event'])

<section class="event-hero" aria-labelledby="event-title">
    <div class="event-hero__media">
        <x-responsive-image
            :src="$event['image']"
            :small="$event['image_small']"
            :medium="$event['image_medium']"
            :alt="$event['image_alt']"
            :width="1400"
            :height="900"
            sizes="(min-width: 80rem) 78rem, calc(100vw - 2rem)"
            :eager="true"
        />
        <div class="event-hero__badges">
            <x-status-badge :label="$event['status_label']" :tone="$event['status_tone']" />
            <x-status-badge :label="str($event['format'])->headline()" tone="surface" />
            @if ($event['verification_label'])
                <x-status-badge :label="$event['verification_label']" icon="badge-check" tone="safe" />
            @endif
        </div>
    </div>

    <div class="event-hero__body">
        <div class="event-hero__heading">
            <div class="event-hero__copy">
                <p class="event-hero__eyebrow">{{ $event['eyebrow'] }}</p>
                <h1 id="event-title">{{ $event['title'] }}</h1>
                <p>{{ $event['long_description'] }}</p>
            </div>
            <div class="event-hero__commands">
                <x-action-control
                    :label="$event['primary_action']['label']"
                    :icon="$event['primary_action']['icon']"
                    :href="$event['primary_action']['href']"
                    :variant="$event['primary_action']['variant']"
                    size="regular"
                />
                @foreach ($event['secondary_actions'] as $action)
                    <x-action-control
                        :label="$action['label']"
                        :icon="$action['icon']"
                        :href="$action['href'] ?? null"
                        :endpoint="$action['endpoint'] ?? null"
                        :payload="$action['payload'] ?? []"
                        :active="$action['active'] ?? false"
                        variant="paper"
                        size="regular"
                    />
                @endforeach
            </div>
        </div>

        <x-detail-meta-list :items="$event['meta']" />
        <x-tag-list :items="$event['tags']" empty="No event topics." class="event-hero__tags" />
        <x-stat-grid
            :items="$event['stats']"
            label="Event summary"
            :icons="['users', 'clock-3', 'ticket-check', 'languages']"
            large
        />
    </div>
</section>
