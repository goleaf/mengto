<x-directory-card
    :data-pet-workspace-profile="$workspace ? '' : null"
    :data-directory-pet="$workspace ? null : ''"
    {{ $attributes }}
>
    <x-slot:media>
        @if ($pet['image'] ?? null)
            <x-card-media
                :src="$pet['image']"
                :small="$pet['image_small'] ?? null"
                :medium="$pet['image_medium'] ?? null"
                :alt="$pet['image_alt']"
                :width="1200"
                :height="$workspace ? 800 : 900"
                sizes="(min-width: 1280px) 560px, (min-width: 640px) calc(50vw - 2rem), calc(100vw - 2rem)"
                :eager="$eager"
                :ratio="$workspace ? 'landscape' : 'portrait'"
                :href="$pet['media_target']['url'] ?? null"
                :link-label="$pet['media_target']['label'] ?? null"
            >
                <x-pet-badge :type="$pet['species']" class="absolute right-3 top-3" />
            </x-card-media>
        @else
            <x-linked-media
                :href="$pet['media_target']['url'] ?? null"
                :label="$pet['media_target']['label'] ?? null"
                variant="card"
            >
                <div
                    class="grid aspect-[3/2] w-full place-items-center bg-paw-mint text-paw-leaf"
                    role="img"
                    aria-label="{{ $pet['image_alt'] }}"
                >
                    <x-ui-icon name="paw-print" size="3xl" />
                </div>
            </x-linked-media>
        @endif
    </x-slot:media>

    <x-card-heading
        :title="$pet['name']"
        :href="$pet['media_target']['url'] ?? null"
        :level="2"
        spacing="none"
    />

    @if ($workspace)
        <p class="mt-1 text-sm text-paw-muted">{{ $pet['details'] }}</p>

        <div class="mt-4 flex flex-wrap gap-2">
            <x-status-badge
                :label="$pet['status']"
                :icon="$pet['status_icon']"
                :tone="$pet['status_tone']"
            />
            <x-status-badge :label="$pet['visibility']" icon="shield-check" tone="surface" />
        </div>

        <x-slot:footer>
            <dl class="grid gap-2 text-sm">
                <div class="flex min-w-0 items-start justify-between gap-3">
                    <dt class="text-paw-muted">{{ __('pet_workspace.relationship') }}</dt>
                    <dd class="min-w-0 text-end font-semibold text-paw-ink">{{ $pet['relationship'] }}</dd>
                </div>
                <div class="flex min-w-0 items-start justify-between gap-3">
                    <dt class="text-paw-muted">{{ __('pet_workspace.discovery') }}</dt>
                    <dd class="min-w-0 text-end font-semibold text-paw-ink">{{ $pet['discoverability'] }}</dd>
                </div>
            </dl>

            <p class="mt-3 text-xs text-paw-muted">{{ $pet['updated'] }}</p>

            <div class="mt-4 flex flex-wrap gap-2">
                @if ($pet['management_url'] !== null)
                    <x-action-control
                        :label="__('pet_workspace.manage_profile', ['name' => $pet['name']])"
                        icon="settings-2"
                        variant="primary"
                        size="regular"
                        :href="$pet['management_url']"
                    />
                @endif
                <x-action-control
                    :label="__('pet_workspace.view_profile', ['name' => $pet['name']])"
                    icon="eye"
                    variant="surface"
                    size="regular"
                    :href="$pet['profile_url']"
                />
            </div>
        </x-slot:footer>
    @else
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
    @endif
</x-directory-card>
