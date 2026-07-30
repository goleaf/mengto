@props(['owner'])

<section data-section="owner" {{ $attributes->merge(['class' => 'panel panel--padded']) }}>
    <p class="text-xs font-semibold uppercase tracking-normal text-paw-leaf">{{ __('ui.lives_with_1d9fff9bde') }}</p>

    <x-owner-identity
        :owner="$owner"
        route-name="profile.mia"
        class="mt-4"
    />

    <p class="mt-4 text-sm leading-6 text-paw-muted">{{ $owner['summary'] }}</p>
</section>
