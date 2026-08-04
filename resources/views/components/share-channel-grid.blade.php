@props([
    'channels' => [],
    'emptyTitle',
    'empty',
])

<div role="list" data-share-channels {{ $attributes->class(['share-channel-list']) }}>
    @forelse ($channels as $channel)
        <x-share-channel-card :channel="$channel" role="listitem" />
    @empty
        <x-empty-state
            icon="share-2"
            :title="$emptyTitle"
            :description="$empty"
            compact
            role="listitem"
        />
    @endforelse
</div>
