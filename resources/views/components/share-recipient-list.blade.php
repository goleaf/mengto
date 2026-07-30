@props([
    'recipients' => [],
    'empty' => __('ui.no_brand_neighbors_are_available_92bb8ec323'),
])

<div role="list" {{ $attributes->class(['share-recipient-list']) }}>
    @forelse ($recipients as $recipient)
        <x-share-recipient-item :recipient="$recipient" role="listitem" />
    @empty
        <x-empty-state
            icon="users"
            title="{{ __('ui.no_neighbors_to_send_to_9a3ba8c390') }}"
            :description="$empty"
            compact
            role="listitem"
        />
    @endforelse
</div>
