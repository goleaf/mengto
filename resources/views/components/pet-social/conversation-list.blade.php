@props(['conversations', 'filters', 'unreadCount'])

<section data-section="conversation-list" {{ $attributes->merge(['class' => 'pc-panel pc-panel--clip']) }}>
    <x-pet-social.conversation-toolbar :filters="$filters" :unread-count="$unreadCount" />

    <div role="list" aria-label="Message conversations">
        @forelse ($conversations as $conversation)
            <x-pet-social.conversation-item :conversation="$conversation" />
        @empty
            <div role="listitem" class="p-6 text-center">
                <h3 class="text-sm font-semibold text-paw-ink">No conversations yet</h3>
                <p class="mt-2 text-sm text-paw-muted">Neighborhood messages will appear here.</p>
            </div>
        @endforelse
    </div>
</section>
