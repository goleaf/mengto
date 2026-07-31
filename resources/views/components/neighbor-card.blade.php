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
        >
            <x-status-badge :label="$neighbor['category']" class="absolute left-3 top-3" />
        </x-card-media>
    </x-slot:media>

    <div class="flex items-center justify-between gap-3 text-xs font-semibold">
        <x-icon-text icon="map-pin" class="meta--accent">
            {{ $neighbor['neighborhood'] }}
        </x-icon-text>
        <x-icon-text icon="navigation" class="meta--nowrap shrink-0">
            {{ $neighbor['distance'] }}
        </x-icon-text>
    </div>

    <h3 class="mt-2 break-words text-lg font-semibold text-paw-ink">
        <x-optional-link
            :route-name="$neighbor['profile_route'] ?? null"
            :route-parameters="$neighbor['profile_parameters'] ?? []"
        >
            {{ $neighbor['name'] }}
        </x-optional-link>
    </h3>
    <p class="mt-1 text-sm font-semibold text-paw-coral">{{ $neighbor['pet'] }}</p>
    <p class="mt-3 text-sm leading-6 text-paw-muted">{{ $neighbor['status'] }}</p>

    <x-tag-list
        :items="$neighbor['interests']"
        empty="{{ __('ui.open_to_new_pet_circles_7cd570a75b') }}"
        reserve
        class="mt-4"
    />

    <div class="mt-auto flex items-center gap-3 border-t border-paw-line pt-5">
        <div class="flex -space-x-2" aria-hidden="true">
            <span class="grid size-8 place-items-center rounded-full border-2 border-white bg-paw-sun text-xs font-semibold text-paw-ink">{{ __('ui.pc_21d017c40a') }}</span>
            <span class="grid size-8 place-items-center rounded-full border-2 border-white bg-paw-mint text-xs font-semibold text-paw-leaf">+{{ $neighbor['mutual_count'] }}</span>
        </div>
        <p class="min-w-0 flex-1 text-xs font-semibold leading-4 text-paw-muted">{{ trans_choice('presentation.mutual_neighbors', $neighbor['mutual_count'], ['count' => $neighbor['mutual_count']]) }}</p>
        <x-action-control
            label="{{ __('ui.follow_641d1ef657') }}"
            active-label="{{ __('ui.following_344b4271ca') }}"
            icon="user-plus"
            active-icon="user-check"
            variant="paper"
            :active="$followed"
            :pressed="$followed"
            :endpoint="route('actions.perform')"
            :payload="['action' => 'toggle-follow', 'target' => $neighborKey, 'label' => $neighbor['name']]"
            class="shrink-0"
        />
    </div>
</x-directory-card>
