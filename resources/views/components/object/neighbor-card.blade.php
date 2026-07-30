@props(['neighbor', 'eager' => false])

@php
    $neighborKey = $neighbor['key'] ?? \Illuminate\Support\Str::slug($neighbor['name']);
    $followed = $neighbor['followed'] ?? false;
@endphp

<x-object.directory-card data-neighbor-card {{ $attributes }}>
    <x-slot:media>
        <x-ui.card-media
            :src="$neighbor['image']"
            :small="$neighbor['image_small'] ?? null"
            :medium="$neighbor['image_medium'] ?? null"
            :alt="$neighbor['image_alt']"
            :width="1200"
            :height="800"
            sizes="(min-width: 1280px) 292px, (min-width: 640px) calc(50vw - 2rem), calc(100vw - 2rem)"
            :eager="$eager"
        >
            <x-ui.status-badge :label="$neighbor['category']" class="absolute left-3 top-3" />
        </x-ui.card-media>
    </x-slot:media>

    <div class="flex items-center justify-between gap-3 text-xs font-semibold">
        <x-ui.icon-text icon="map-pin" class="meta--accent">
            {{ $neighbor['neighborhood'] }}
        </x-ui.icon-text>
        <x-ui.icon-text icon="navigation" class="meta--nowrap shrink-0">
            {{ $neighbor['distance'] }}
        </x-ui.icon-text>
    </div>

    <h3 class="mt-2 break-words text-lg font-semibold text-paw-ink">
        <x-ui.optional-link
            :route-name="$neighbor['profile_route'] ?? null"
            :route-parameters="$neighbor['profile_parameters'] ?? []"
        >
            {{ $neighbor['name'] }}
        </x-ui.optional-link>
    </h3>
    <p class="mt-1 text-sm font-semibold text-paw-coral">{{ $neighbor['pet'] }}</p>
    <p class="mt-3 text-sm leading-6 text-paw-muted">{{ $neighbor['status'] }}</p>

    <x-ui.tag-list
        :items="$neighbor['interests']"
        empty="Open to new pet circles."
        reserve
        class="mt-4"
    />

    <div class="mt-auto flex items-center gap-3 border-t border-paw-line pt-5">
        <div class="flex -space-x-2" aria-hidden="true">
            <span class="grid size-8 place-items-center rounded-full border-2 border-white bg-paw-sun text-xs font-semibold text-paw-ink">PC</span>
            <span class="grid size-8 place-items-center rounded-full border-2 border-white bg-paw-mint text-xs font-semibold text-paw-leaf">+{{ $neighbor['mutual_count'] }}</span>
        </div>
        <p class="min-w-0 flex-1 text-xs font-semibold leading-4 text-paw-muted">{{ $neighbor['mutual_count'] }} mutual neighbors</p>
        <x-ui.action-control
            label="Follow"
            active-label="Following"
            icon="user-plus"
            active-icon="user-check"
            variant="paper"
            :active="$followed"
            :pressed="$followed"
            :endpoint="route('pet-social.actions.perform')"
            :payload="['action' => 'toggle-follow', 'target' => $neighborKey, 'label' => $neighbor['name']]"
            class="shrink-0"
        />
    </div>
</x-object.directory-card>
