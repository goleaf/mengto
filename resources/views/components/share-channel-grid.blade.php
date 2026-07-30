@props([
    'channels' => [],
    'empty' => __('ui.no_sharing_channels_are_available_486d524105'),
])

<div role="list" {{ $attributes->class(['share-channel-list']) }}>
    @forelse ($channels as $channel)
        <x-share-channel-card :channel="$channel" role="listitem" />
    @empty
        <x-empty-state
            icon="share-2"
            title="{{ __('ui.no_sharing_channels_d3390ed990') }}"
            :description="$empty"
            compact
            role="listitem"
        />
    @endforelse
</div>
