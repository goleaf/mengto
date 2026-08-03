<x-directory-card data-meetup-card {{ $attributes }}>
    <x-slot:media>
        <x-card-media
            :src="$meetup['image']"
            :small="$meetup['image_small'] ?? null"
            :medium="$meetup['image_medium'] ?? null"
            :alt="$meetup['image_alt']"
            :width="1200"
            :height="800"
            sizes="(min-width: 1280px) 390px, (min-width: 640px) calc(50vw - 2rem), calc(100vw - 2rem)"
            :eager="$eager"
            :href="$meetup['media_target']['url'] ?? null"
            :link-label="$meetup['media_target']['label'] ?? null"
        >
            <x-status-badge :label="$meetup['category']" class="absolute left-3 top-3" />
            <time datetime="{{ $meetup['datetime'] }}" aria-label="{{ $meetup['date_accessible'] }}" data-meetup-date class="absolute right-3 top-3 grid min-w-12 place-items-center rounded-md border border-white/70 bg-paw-ink px-2 py-1.5 text-center text-white shadow-sm">
                <span class="text-xs font-semibold">{{ $meetup['day'] }}</span>
                <span class="text-lg font-semibold leading-5">{{ $meetup['date'] }}</span>
            </time>
        </x-card-media>
    </x-slot:media>

    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold text-paw-coral">
        <time datetime="{{ $meetup['datetime'] }}" aria-label="{{ $meetup['date_accessible'] }}">{{ $meetup['date_label'] }} · {{ $meetup['time'] }}</time>
        <x-icon-text icon="navigation" class="meta--nowrap">
            {{ $meetup['distance'] }}
        </x-icon-text>
    </div>
    <h3 class="mt-2 break-words text-lg font-semibold text-paw-ink">
        <x-optional-link
            :href="$meetup['media_target']['url'] ?? null"
        >
            {{ $meetup['title'] }}
        </x-optional-link>
    </h3>
    <p class="mt-2 text-sm leading-6 text-paw-muted">{{ $meetup['description'] }}</p>

    <x-tag-list :items="$meetup['tags']" empty="{{ __('ui.all_friendly_pets_welcome_1319984225') }}" reserve class="mt-4" />

    <div class="mt-5 border-t border-paw-line pt-4">
        <x-icon-text icon="map-pin" class="meta--strong">
            {{ $meetup['place'] }}
        </x-icon-text>
        <p class="mt-1 text-xs text-paw-muted">{{ $meetup['neighborhood'] }}</p>
    </div>

    <x-initials-action-row
        :initials="$meetup['host_initials']"
        :title="__('ui.hosted_by_f772bf2712').' '.$meetup['host']"
        :detail="$meetup['attendees']"
        detail-icon="users"
        action-label="{{ __('ui.rsvp_1dfe8a8e0c') }}"
        action-icon="calendar-plus"
        :action-endpoint="route('actions.perform')"
        :action-payload="['action' => 'toggle-meetup', 'target' => $meetupKey, 'label' => $meetup['title']]"
        :active="$rsvp"
        active-label="{{ __('ui.going_7bd49cdc7d') }}"
        active-icon="calendar-check"
    />
</x-directory-card>
