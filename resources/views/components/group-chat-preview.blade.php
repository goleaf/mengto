@props(['messages'])

<x-content-panel
    section="group-chat"
    eyebrow="{{ __('ui.member_chat_1a3ace7995') }}"
    title="{{ __('ui.planning_channel_deae8b6ad5') }}"
>
    <div class="chat-preview" role="log" aria-label="{{ __('ui.recent_group_messages_4c2bce3e4a') }}">
        @forelse ($messages as $message)
            <article class="chat-preview__message">
                <x-initials-avatar
                    :initials="$message['initials']"
                    :tone="$message['tone']"
                    size="compact"
                />
                <div>
                    <p><strong>{{ $message['name'] }}</strong> <time>{{ $message['time'] }}</time></p>
                    <span>{{ $message['body'] }}</span>
                </div>
            </article>
        @empty
            <p class="group-dashboard__empty">{{ __('ui.no_recent_messages_are_available_a23afc204b') }}</p>
        @endforelse
    </div>

    <x-action-control
        label="{{ __('ui.open_chat_0600175af8') }}"
        icon="message-circle"
        variant="paper"
        class="section-body"
        :href="route('messages.index')"
    />
</x-content-panel>
