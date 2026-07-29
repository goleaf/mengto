@props(['filters', 'unreadCount'])

<div class="border-b border-paw-line p-4">
    <x-pet-social.panel-heading title="Conversations" :meta="$unreadCount.' unread'" />

    <x-pet-social.search-field
        id="conversation-search"
        label="Search conversations"
        placeholder="Search messages"
        class="mt-4"
    />

    <div class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Conversation filters">
        @forelse ($filters as $filter)
            <x-pet-social.filter-chip :label="$filter" :active="$loop->first" />
        @empty
            <span class="text-sm text-paw-muted">Filters unavailable.</span>
        @endforelse
    </div>
</div>
