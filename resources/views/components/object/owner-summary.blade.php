@props(['owner'])

<section data-section="owner" {{ $attributes->merge(['class' => 'panel panel--padded']) }}>
    <p class="text-xs font-semibold uppercase tracking-normal text-paw-leaf">Lives with</p>

    <x-object.owner-identity
        :owner="$owner"
        route-name="pet-social.profile.mia"
        class="mt-4"
    />

    <p class="mt-4 text-sm leading-6 text-paw-muted">{{ $owner['summary'] }}</p>
</section>
