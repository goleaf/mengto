@props(['pet', 'eager' => false])

<x-pet-social.directory-card data-directory-pet {{ $attributes }}>
    <x-slot:media>
        <x-pet-social.card-media
            :src="$pet['image']"
            :small="$pet['image_small'] ?? null"
            :medium="$pet['image_medium'] ?? null"
            :alt="$pet['image_alt']"
            :width="1200"
            :height="900"
            sizes="(min-width: 1280px) 390px, (min-width: 640px) calc(50vw - 2rem), calc(100vw - 2rem)"
            :eager="$eager"
            ratio="portrait"
        >
            <x-pet-social.pet-badge :type="$pet['species']" class="absolute right-3 top-3" />
        </x-pet-social.card-media>
    </x-slot:media>

    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            @if ($pet['profile_route'])
                <h2 class="truncate text-lg font-semibold text-paw-ink">
                    <x-pet-social.text-link :href="route($pet['profile_route'])">
                        {{ $pet['name'] }}
                    </x-pet-social.text-link>
                </h2>
            @else
                <h2 class="truncate text-lg font-semibold text-paw-ink">{{ $pet['name'] }}</h2>
            @endif
            <p class="mt-1 text-sm text-paw-muted">{{ $pet['breed'] }} · {{ $pet['age'] }}</p>
        </div>
    </div>

    <p class="mt-4 text-sm font-medium text-paw-coral">{{ $pet['status'] }}</p>

    <x-pet-social.tag-list :items="$pet['traits']" empty="No traits shared." reserve class="mt-4" />

    <div class="mt-5 border-t border-paw-line pt-4">
        <p class="text-sm font-semibold text-paw-ink">With {{ $pet['owner'] }}</p>
        <x-pet-social.icon-text icon="map-pin" class="mt-1">
            {{ $pet['neighborhood'] }} · Portland, OR
        </x-pet-social.icon-text>

        <div class="mt-4 flex items-center gap-3">
            @if ($pet['profile_route'])
                <x-pet-social.text-link :href="route($pet['profile_route'])" icon="eye" variant="action">
                    View profile
                </x-pet-social.text-link>
            @endif

            <x-pet-social.static-action label="Follow" icon="user-plus" variant="paper" class="ml-auto shrink-0" />
        </div>
    </div>
</x-pet-social.directory-card>
