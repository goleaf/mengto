@props(['owner'])

<section data-section="owner" {{ $attributes->merge(['class' => 'pc-panel pc-panel--padded']) }}>
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-paw-leaf">Lives with</p>

    <div class="mt-4 flex items-center gap-3">
        <x-pet-social.avatar :src="$owner['avatar']" :alt="$owner['name']" />
        <div class="min-w-0">
            <h2 class="truncate text-base font-semibold text-paw-ink">{{ $owner['name'] }}</h2>
            <x-pet-social.icon-text icon="map-pin" class="mt-1">{{ $owner['location'] }}</x-pet-social.icon-text>
        </div>
    </div>

    <p class="mt-4 text-sm leading-6 text-paw-muted">{{ $owner['summary'] }}</p>
</section>
