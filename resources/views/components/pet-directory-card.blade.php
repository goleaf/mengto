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
            :href="$pet['media_target']['url'] ?? null"
            :link-label="$pet['media_target']['label'] ?? null"
        >
            <x-pet-badge :type="$pet['species']" class="absolute right-3 top-3" />
        </x-card-media>
    </x-slot:media>

    <x-card-heading
        :title="$pet['name']"
        :href="$pet['media_target']['url'] ?? null"
        :level="2"
        spacing="none"
    />
    <p class="mt-1 text-sm text-paw-muted">{{ $pet['breed'] }} · {{ $pet['age'] }}</p>

    <p class="mt-4 text-sm font-medium text-paw-coral">{{ $pet['status'] }}</p>

    <x-tag-list :items="$pet['traits']" empty="{{ __('ui.no_traits_shared_251b121ad1') }}" reserve class="mt-4" />

    <x-slot:footer>
        <p class="text-sm font-semibold text-paw-ink">{{ __('presentation.with_owner', ['owner' => $pet['owner']]) }}</p>
        <x-icon-text icon="map-pin" class="mt-1">
            {{ __('presentation.neighborhood_location', ['neighborhood' => $pet['neighborhood']]) }}
        </x-icon-text>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            @if ($pet['profile_route'] ?? null)
                <x-text-link
                    :href="$pet['media_target']['url']"
                    icon="eye"
                    variant="action"
                >
                    {{ __('ui.view_profile_d4788f256f') }}
                </x-text-link>
            @endif

            <x-action-control
                label="{{ __('ui.follow_641d1ef657') }}"
                active-label="{{ __('ui.following_344b4271ca') }}"
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
    </x-slot:footer>
</x-directory-card>
