@props(['owner'])

<section data-section="owner" {{ $attributes->merge(['class' => 'panel panel--padded']) }}>
    <p class="text-xs font-semibold uppercase tracking-normal text-paw-leaf">{{ __('ui.lives_with') }}</p>

    <x-owner-identity
        :owner="$owner"
        :href="$owner['media_target']['url'] ?? null"
        :link-label="$owner['media_target']['label'] ?? null"
        class="mt-4"
    />

    <p class="mt-4 text-sm leading-6 text-paw-muted">{{ $owner['summary'] }}</p>
</section>
