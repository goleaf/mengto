@props([
    'items',
    'empty',
])

<div class="connection-list" role="list">
    @forelse ($items as $item)
        <div role="listitem">
            <x-object.connection-card :item="$item" />
        </div>
    @empty
        <x-ui.empty-state
            :icon="$empty['icon']"
            :title="$empty['title']"
            :description="$empty['description']"
            compact
        />
    @endforelse
</div>
