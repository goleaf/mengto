@props(['owner', 'pets' => []])

<section {{ $attributes->merge(['class' => 'panel panel--padded']) }}>
    <x-owner-identity :owner="$owner" avatar-size="profile" variant="profile" />

    <p class="mt-4 text-sm leading-6 text-paw-muted">{{ $owner['summary'] }}</p>

    <div data-section="pets" class="mt-5 border-t border-paw-line pt-5">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-xs font-semibold text-paw-leaf">{{ __('ui.your_pets_d1e438a50f') }}</h3>
            <x-action-control
                :href="route('compose', 'pet')"
                label="{{ __('ui.add_9fd728c66c') }}"
                icon="plus"
                variant="quiet"
                size="micro"
            />
        </div>

        <div role="list" class="mt-4 grid gap-3">
            @forelse ($pets as $pet)
                <x-profile-card-pet :pet="$pet" />
            @empty
                <p role="listitem" class="text-sm text-paw-muted">{{ __('ui.no_pets_added_yet_2b20beeaa2') }}</p>
            @endforelse
        </div>
    </div>

    <x-action-control
        :href="route('circle.index')"
        label="{{ __('ui.open_my_circle_5b3bad3571') }}"
        icon="bookmark"
        variant="paper"
        size="regular"
        class="mt-5 w-full"
    />
</section>
