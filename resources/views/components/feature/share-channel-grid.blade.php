@props([
    'channels' => [],
    'empty' => 'No sharing channels are available.',
])

<div role="list" {{ $attributes->class(['share-channel-list']) }}>
    @forelse ($channels as $channel)
        <x-object.share-channel-card :channel="$channel" role="listitem" />
    @empty
        <x-ui.empty-state
            icon="share-2"
            title="No sharing channels"
            :description="$empty"
            compact
            role="listitem"
        />
    @endforelse
</div>
