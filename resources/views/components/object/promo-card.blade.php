@props([
    'item',
    'section',
    'attendees' => null,
    'eager' => false,
])

<section
    data-section="{{ $section }}"
    {{ $attributes->class(['panel', 'panel--clip', 'promo-card']) }}
>
    <x-ui.responsive-image
        :src="$item['image']"
        :small="$item['image_small'] ?? null"
        :medium="$item['image_medium'] ?? null"
        :alt="$item['image_alt']"
        :width="1200"
        :height="800"
        sizes="(min-width: 1024px) 320px, (min-width: 768px) calc(33vw - 2rem), calc(100vw - 2rem)"
        :eager="$eager"
        class="promo-card__image"
    />

    <div class="promo-card__body">
        <x-ui.section-heading
            :eyebrow="$item['eyebrow']"
            :title="$item['title']"
            size="compact"
            tone="coral"
        />

        <time
            datetime="{{ $item['datetime'] }}"
            aria-label="{{ $item['date_accessible'] }}"
            class="promo-card__date"
        >
            <x-lucide-calendar-days class="icon icon--sm" aria-hidden="true" />
            <span>{{ $item['date'] }}</span>
        </time>
        <x-ui.icon-text icon="map-pin" class="meta--strong mt-1">
            {{ $item['place'] }}
        </x-ui.icon-text>

        @if ($attendees)
            <x-ui.icon-text icon="users" class="mt-3">{{ $attendees }}</x-ui.icon-text>
        @endif
    </div>
</section>
