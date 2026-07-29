@props([
    'item',
    'section',
    'attendees' => null,
    'eager' => false,
])

<section
    data-section="{{ $section }}"
    {{ $attributes->class(['pc-panel', 'pc-panel--clip', 'pc-promo-card']) }}
>
    <x-pet-social.responsive-image
        :src="$item['image']"
        :small="$item['image_small'] ?? null"
        :medium="$item['image_medium'] ?? null"
        :alt="$item['image_alt']"
        :width="1200"
        :height="800"
        sizes="(min-width: 1024px) 320px, (min-width: 768px) calc(33vw - 2rem), calc(100vw - 2rem)"
        :eager="$eager"
        class="pc-promo-card__image"
    />

    <div class="pc-promo-card__body">
        <x-pet-social.section-heading
            :eyebrow="$item['eyebrow']"
            :title="$item['title']"
            size="compact"
            tone="coral"
        />

        <time
            datetime="{{ $item['datetime'] }}"
            aria-label="{{ $item['date_accessible'] }}"
            class="pc-promo-card__date"
        >
            <x-lucide-calendar-days class="pc-icon pc-icon--sm" aria-hidden="true" />
            <span>{{ $item['date'] }}</span>
        </time>
        <x-pet-social.icon-text icon="map-pin" class="pc-meta--strong mt-1">
            {{ $item['place'] }}
        </x-pet-social.icon-text>

        @if ($attendees)
            <x-pet-social.icon-text icon="users" class="mt-3">{{ $attendees }}</x-pet-social.icon-text>
        @endif
    </div>
</section>
