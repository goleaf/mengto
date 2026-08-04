@props([
    'recipients' => [],
    'emptyTitle',
    'empty',
])

<div role="list" data-share-recipients {{ $attributes->class(['share-recipient-list']) }}>
    @forelse ($recipients as $recipient)
        <x-share-recipient-item :recipient="$recipient" role="listitem" />
    @empty
        <x-empty-state
            icon="users"
            :title="$emptyTitle"
            :description="$empty"
            compact
            role="listitem"
        />
    @endforelse
</div>
