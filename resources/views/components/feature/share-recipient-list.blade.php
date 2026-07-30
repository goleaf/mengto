@props([
    'recipients' => [],
    'empty' => 'No PawCircle neighbors are available.',
])

<div role="list" {{ $attributes->class(['share-recipient-list']) }}>
    @forelse ($recipients as $recipient)
        <x-object.share-recipient-item :recipient="$recipient" role="listitem" />
    @empty
        <x-ui.empty-state
            icon="users"
            title="No neighbors to send to"
            :description="$empty"
            compact
            role="listitem"
        />
    @endforelse
</div>
