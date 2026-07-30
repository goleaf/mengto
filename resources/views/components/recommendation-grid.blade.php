@props([
    'items',
    'empty',
])

<div class="recommendation-grid" role="list">
    @forelse ($items as $item)
        <div role="listitem">
            <x-connection-card :item="$item" variant="recommendation" />
        </div>
    @empty
        <x-empty-state
            :icon="$empty['icon']"
            :title="$empty['title']"
            :description="$empty['description']"
            compact
        />
    @endforelse
</div>
