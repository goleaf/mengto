@props([
    'items',
    'empty',
    'endpoint',
    'clearHref' => null,
])

<section class="pet-friend-list" aria-live="polite">
    @forelse ($items as $item)
        <x-pet-friend-card :item="$item" :endpoint="$endpoint" />
    @empty
        <x-empty-state
            :icon="$empty['icon']"
            :title="$empty['title']"
            :description="$empty['description']"
            :href="$clearHref"
        />
    @endforelse
</section>
