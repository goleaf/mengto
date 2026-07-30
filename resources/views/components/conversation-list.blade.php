@props(['conversations', 'filters', 'unreadCount', 'query' => '', 'activeFilter' => 'all'])

<section data-section="conversation-list" {{ $attributes->merge(['class' => 'panel panel--clip']) }}>
    <x-conversation-toolbar
        :filters="$filters"
        :unread-count="$unreadCount"
        :query="$query"
        :active-filter="$activeFilter"
    />

    <div role="list" aria-label="{{ __('ui.message_conversations_e4bd6596ef') }}">
        @forelse ($conversations as $conversation)
            <x-conversation-item :conversation="$conversation" />
        @empty
            <div role="listitem" class="p-6 text-center">
                <h3 class="text-sm font-semibold text-paw-ink">{{ __('ui.no_conversations_yet_0d60084f05') }}</h3>
                <p class="mt-2 text-sm text-paw-muted">{{ __('ui.neighborhood_messages_will_appear_here_a9b7f0fea2') }}</p>
            </div>
        @endforelse
    </div>
</section>
