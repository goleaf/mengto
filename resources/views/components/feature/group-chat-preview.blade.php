@props(['messages'])

<x-ui.content-panel
    section="group-chat"
    eyebrow="Member chat"
    title="Planning channel"
>
    <div class="chat-preview" role="log" aria-label="Recent group messages">
        @forelse ($messages as $message)
            <article class="chat-preview__message">
                <x-ui.initials-avatar
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
            <p class="group-dashboard__empty">No recent messages are available.</p>
        @endforelse
    </div>

    <x-ui.action-control
        label="Open chat"
        icon="message-circle"
        variant="paper"
        class="section-body"
        :href="route('pet-social.messages.index')"
    />
</x-ui.content-panel>
