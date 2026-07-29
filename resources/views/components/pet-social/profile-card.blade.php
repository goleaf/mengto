@props(['owner', 'pets' => []])

<section {{ $attributes->merge(['class' => 'pc-panel pc-panel--padded']) }}>
    <div class="flex items-start gap-4">
        <x-pet-social.avatar :src="$owner['avatar']" :alt="$owner['name']" size="profile" />
        <div class="min-w-0">
            <h2 class="truncate text-base font-semibold text-paw-ink">{{ $owner['name'] }}</h2>
            <x-pet-social.icon-text icon="map-pin" class="mt-1">{{ $owner['location'] }}</x-pet-social.icon-text>
        </div>
    </div>

    <p class="mt-4 text-sm leading-6 text-paw-muted">{{ $owner['summary'] }}</p>

    <div data-section="pets" class="mt-5 border-t border-paw-line pt-5">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-xs font-semibold uppercase tracking-[0.16em] text-paw-leaf">Your pets</h3>
            <x-pet-social.static-action label="Add" icon="plus" variant="quiet" size="micro" />
        </div>

        <div role="list" class="mt-4 grid gap-3">
            @forelse ($pets as $pet)
                <article role="listitem" class="border-b border-paw-line pb-3 last:border-b-0 last:pb-0">
                    <div class="flex items-center justify-between gap-3">
                        @if ($pet['profile_route'])
                            <h4 class="min-w-0 text-sm font-semibold text-paw-ink">
                                <x-pet-social.text-link :href="route($pet['profile_route'])" data-profile-link>
                                    {{ $pet['name'] }}
                                </x-pet-social.text-link>
                            </h4>
                        @else
                            <h4 class="min-w-0 text-sm font-semibold text-paw-ink">{{ $pet['name'] }}</h4>
                        @endif
                        <x-pet-social.pet-badge :type="$pet['type']" tone="sun" />
                    </div>
                    <p class="mt-1 text-sm text-paw-muted">{{ $pet['breed'] }} · {{ $pet['age'] }}</p>
                    <p class="mt-2 text-xs font-medium text-paw-coral">{{ $pet['status'] }}</p>
                </article>
            @empty
                <p role="listitem" class="text-sm text-paw-muted">No pets added yet.</p>
            @endforelse
        </div>
    </div>
</section>
