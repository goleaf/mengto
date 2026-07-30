@props([
    'items',
    'empty',
    'endpoint',
    'clearHref' => null,
])

<section class="pet-friend-list" aria-live="polite">
    @forelse ($items as $item)
        <x-object.pet-friend-card :item="$item" :endpoint="$endpoint" />
    @empty
        <x-ui.empty-state
            :icon="$empty['icon']"
            :title="$empty['title']"
            :description="$empty['description']"
            :href="$clearHref"
        />
    @endforelse
</section>
