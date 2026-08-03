<x-directory-card data-neighbor-card {{ $attributes }}>
    <x-slot:media>
        <x-card-media
            :src="$neighbor['image']"
            :small="$neighbor['image_small'] ?? null"
            :medium="$neighbor['image_medium'] ?? null"
            :alt="$neighbor['image_alt']"
            :width="1200"
            :height="800"
            sizes="(min-width: 1280px) 390px, (min-width: 640px) calc(50vw - 2rem), calc(100vw - 2rem)"
            :eager="$eager"
            :href="$neighbor['media_target']['url'] ?? null"
            :link-label="$neighbor['media_target']['label'] ?? null"
        >
            <x-status-badge
                :label="$neighbor['category']"
                :icon="$neighbor['category_icon']"
                class="absolute left-3 top-3"
                data-neighbor-card-category
            />
        </x-card-media>
    </x-slot:media>

    <div class="flex items-center justify-between gap-3 text-xs font-semibold">
        <x-icon-text icon="map-pin" class="meta--accent" data-neighbor-card-neighborhood>
            {{ $neighbor['neighborhood'] }}
        </x-icon-text>
        <x-icon-text icon="navigation" class="meta--nowrap shrink-0" data-neighbor-card-distance>
            {{ $neighbor['distance'] }}
        </x-icon-text>
    </div>

    <x-card-heading
        :title="$neighbor['name']"
        :href="$neighbor['media_target']['url'] ?? null"
    />
    <p class="mt-1 text-sm font-semibold text-paw-coral" data-neighbor-card-pet>{{ $neighbor['pet'] }}</p>
    <x-card-description spacing="relaxed" data-neighbor-card-status>{{ $neighbor['status'] }}</x-card-description>

    <x-tag-list
        :items="$neighbor['interests']"
        empty="{{ __('neighbors.card.empty_interests') }}"
        reserve
        class="mt-4"
        data-neighbor-card-interests
    />

    <x-slot:footer>
        <div class="flex min-w-0 items-center gap-3">
            <div class="flex -space-x-2" aria-hidden="true">
                <span class="grid size-8 place-items-center rounded-full border-2 border-white bg-paw-sun text-xs font-semibold text-paw-ink">{{ __('neighbors.card.brand_initials') }}</span>
                <span class="grid size-8 place-items-center rounded-full border-2 border-white bg-paw-mint text-xs font-semibold text-paw-leaf">+{{ $neighbor['mutual_count'] }}</span>
            </div>
            <p class="min-w-0 flex-1 text-xs font-semibold leading-4 text-paw-muted">{{ trans_choice('presentation.mutual_neighbors', $neighbor['mutual_count'], ['count' => $neighbor['mutual_count']]) }}</p>
            <x-action-control
                label="{{ __('neighbors.card.follow') }}"
                active-label="{{ __('neighbors.card.following') }}"
                icon="user-plus"
                active-icon="user-check"
                variant="paper"
                :active="$followed"
                :pressed="$followed"
                :endpoint="route('actions.perform')"
                :payload="['action' => 'toggle-follow', 'target' => $neighborKey, 'label' => $neighbor['name']]"
                class="shrink-0"
                data-neighbor-card-follow
            />
        </div>
    </x-slot:footer>
</x-directory-card>
