@props(['owner', 'pets' => []])

<section {{ $attributes->merge(['class' => 'panel panel--padded']) }}>
    <x-object.owner-identity :owner="$owner" avatar-size="profile" variant="profile" />

    <p class="mt-4 text-sm leading-6 text-paw-muted">{{ $owner['summary'] }}</p>

    <div data-section="pets" class="mt-5 border-t border-paw-line pt-5">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-xs font-semibold text-paw-leaf">Your pets</h3>
            <x-ui.action-control
                :href="route('compose', 'pet')"
                label="Add"
                icon="plus"
                variant="quiet"
                size="micro"
            />
        </div>

        <div role="list" class="mt-4 grid gap-3">
            @forelse ($pets as $pet)
                <x-object.profile-card-pet :pet="$pet" />
            @empty
                <p role="listitem" class="text-sm text-paw-muted">No pets added yet.</p>
            @endforelse
        </div>
    </div>

    <x-ui.action-control
        :href="route('circle.index')"
        label="Open my circle"
        icon="bookmark"
        variant="paper"
        size="regular"
        class="mt-5 w-full"
    />
</section>
