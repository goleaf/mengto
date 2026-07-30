@props(['event'])

<article class="event-card">
    <a
        href="{{ $event['primary_action']['href'] }}"
        class="event-card__media"
        aria-label="Open {{ $event['title'] }}"
    >
        <x-responsive-image
            :src="$event['image']"
            :small="$event['image_small'] ?? null"
            :medium="$event['image_medium'] ?? null"
            :alt="$event['image_alt']"
            :width="900"
            :height="600"
            sizes="(min-width: 80rem) 26rem, (min-width: 48rem) 50vw, 100vw"
        />
        <span class="event-card__date" aria-hidden="true">
            <small>{{ $event['day'] }}</small>
            <strong>{{ $event['date'] }}</strong>
        </span>
        <span class="event-card__badges">
            <x-status-badge :label="$event['status_label']" :tone="$event['status_tone']" />
            @if ($event['commercial_label'] ?? null)
                <x-status-badge :label="$event['commercial_label']" tone="surface" />
            @endif
        </span>
    </a>

    <div class="event-card__body">
        <div class="event-card__heading">
            <div>
                <p>{{ $event['category'] }} · {{ $event['format_label'] }}</p>
                <h2>
                    <a href="{{ $event['primary_action']['href'] }}">{{ $event['title'] }}</a>
                </h2>
            </div>
            <span class="event-card__price">{{ $event['price_label'] }}</span>
        </div>

        <p class="event-card__description">{{ $event['short_description'] }}</p>

        <dl class="event-card__facts">
            <div>
                <x-lucide-clock-3 class="icon icon--sm" aria-hidden="true" />
                <dt class="sr-only">Time</dt>
                <dd><time datetime="{{ $event['datetime'] }}">{{ $event['date_label'] }} · {{ $event['time'] }}</time></dd>
            </div>
            <div>
                <x-lucide-map-pin class="icon icon--sm" aria-hidden="true" />
                <dt class="sr-only">Place</dt>
                <dd>{{ $event['place'] }} · {{ $event['distance'] }}</dd>
            </div>
            <div>
                <x-lucide-users class="icon icon--sm" aria-hidden="true" />
                <dt class="sr-only">Capacity</dt>
                <dd>{{ $event['attendees'] }} · {{ $event['capacity_label'] }}</dd>
            </div>
        </dl>

        @if ($event['recommendation_reason'] ?? null)
            <p class="event-card__reason">
                <x-lucide-sparkles class="icon icon--sm" aria-hidden="true" />
                <span>{{ $event['recommendation_reason'] }}</span>
            </p>
        @endif

        <x-tag-list :items="$event['tags']" empty="No event tags." class="event-card__tags" />

        <div class="event-card__footer">
            <div class="event-card__organizer">
                <x-initials-avatar :initials="$event['organizer_initials']" tone="mint" />
                <div>
                    <p>{{ $event['organizer'] }}</p>
                    <span>{{ $event['verification_label'] ?? \Illuminate\Support\Str::headline($event['organizer_type']) }}</span>
                </div>
            </div>
            <div class="event-card__actions">
                @if ($event['interest_action'] ?? null)
                    <x-action-control
                        :label="$event['interest_action']['label']"
                        :icon="$event['interest_action']['icon']"
                        :endpoint="$event['interest_action']['endpoint']"
                        :payload="$event['interest_action']['payload']"
                        :active="$event['interest_action']['active']"
                        :pressed="$event['interest_action']['active']"
                        variant="paper"
                    />
                @endif
                <x-action-control
                    :label="$event['primary_action']['label']"
                    :icon="$event['primary_action']['icon']"
                    :href="$event['primary_action']['href']"
                    :variant="$event['primary_action']['variant']"
                />
            </div>
        </div>
    </div>
</article>
