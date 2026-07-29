@props(['meetup', 'eager' => false])

<x-pet-social.directory-card data-meetup-card {{ $attributes }}>
    <x-slot:media>
        <x-pet-social.card-media
            :src="$meetup['image']"
            :small="$meetup['image_small'] ?? null"
            :medium="$meetup['image_medium'] ?? null"
            :alt="$meetup['image_alt']"
            :width="1200"
            :height="800"
            sizes="(min-width: 1280px) 390px, (min-width: 640px) calc(50vw - 2rem), calc(100vw - 2rem)"
            :eager="$eager"
        >
            <x-pet-social.status-badge :label="$meetup['category']" class="absolute left-3 top-3" />
            <time datetime="{{ $meetup['datetime'] }}" aria-label="{{ $meetup['date_accessible'] }}" data-meetup-date class="absolute right-3 top-3 grid min-w-12 place-items-center rounded-md border border-white/70 bg-paw-ink px-2 py-1.5 text-center text-white shadow-sm">
                <span class="text-[0.65rem] font-semibold">{{ $meetup['day'] }}</span>
                <span class="text-lg font-semibold leading-5">{{ $meetup['date'] }}</span>
            </time>
        </x-pet-social.card-media>
    </x-slot:media>

    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold text-paw-coral">
        <time datetime="{{ $meetup['datetime'] }}" aria-label="{{ $meetup['date_accessible'] }}">{{ $meetup['date_label'] }} · {{ $meetup['time'] }}</time>
        <x-pet-social.icon-text icon="navigation" class="pc-meta--nowrap">
            {{ $meetup['distance'] }}
        </x-pet-social.icon-text>
    </div>
    <h3 class="mt-2 break-words text-lg font-semibold text-paw-ink">{{ $meetup['title'] }}</h3>
    <p class="mt-2 text-sm leading-6 text-paw-muted">{{ $meetup['description'] }}</p>

    <x-pet-social.tag-list :items="$meetup['tags']" empty="All friendly pets welcome." reserve class="mt-4" />

    <div class="mt-5 border-t border-paw-line pt-4">
        <x-pet-social.icon-text icon="map-pin" class="pc-meta--strong">
            {{ $meetup['place'] }}
        </x-pet-social.icon-text>
        <p class="mt-1 text-xs text-paw-muted">{{ $meetup['neighborhood'] }}</p>
    </div>

    <div class="mt-auto flex items-center gap-3 pt-5">
        <x-pet-social.initials-avatar :initials="$meetup['host_initials']" />
        <div class="min-w-0">
            <p class="truncate text-xs font-semibold text-paw-ink">Hosted by {{ $meetup['host'] }}</p>
            <x-pet-social.icon-text icon="users" class="mt-0.5">
                {{ $meetup['attendees'] }}
            </x-pet-social.icon-text>
        </div>
        <x-pet-social.static-action label="RSVP" icon="calendar-plus" variant="paper" class="ml-auto shrink-0" />
    </div>
</x-pet-social.directory-card>
