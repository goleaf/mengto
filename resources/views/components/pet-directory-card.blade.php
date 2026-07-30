<x-directory-card data-directory-pet {{ $attributes }}>
    <x-slot:media>
        <x-card-media
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
            <x-pet-badge :type="$pet['species']" class="absolute right-3 top-3" />
        </x-card-media>
    </x-slot:media>

    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <h2 class="text-lg font-semibold text-paw-ink">
                <x-optional-link
                    :route-name="$pet['profile_route'] ?? null"
                    :route-parameters="$pet['profile_parameters'] ?? []"
                >
                    {{ $pet['name'] }}
                </x-optional-link>
            </h2>
            <p class="mt-1 text-sm text-paw-muted">{{ $pet['breed'] }} · {{ $pet['age'] }}</p>
        </div>
    </div>

    <p class="mt-4 text-sm font-medium text-paw-coral">{{ $pet['status'] }}</p>

    <x-tag-list :items="$pet['traits']" empty="No traits shared." reserve class="mt-4" />

    <div class="mt-5 border-t border-paw-line pt-4">
        <p class="text-sm font-semibold text-paw-ink">With {{ $pet['owner'] }}</p>
        <x-icon-text icon="map-pin" class="mt-1">
            {{ $pet['neighborhood'] }} · Portland, OR
        </x-icon-text>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            @if ($pet['profile_route'] ?? null)
                <x-text-link
                    :route-name="$pet['profile_route']"
                    :route-parameters="$pet['profile_parameters'] ?? []"
                    icon="eye"
                    variant="action"
                >
                    View profile
                </x-text-link>
            @endif

            <x-action-control
                label="Follow"
                active-label="Following"
                icon="user-plus"
                active-icon="user-check"
                variant="paper"
                :active="$followed"
                :pressed="$followed"
                :endpoint="route('actions.perform')"
                :payload="['action' => 'toggle-follow', 'target' => $petKey, 'label' => $pet['name']]"
                class="ml-auto shrink-0"
            />
        </div>
    </div>
</x-directory-card>
