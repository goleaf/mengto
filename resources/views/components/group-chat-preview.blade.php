@props(['messages'])

<x-content-panel
    section="group-chat"
    eyebrow="{{ __('groups.detail.chat.eyebrow') }}"
    title="{{ __('groups.detail.chat.title') }}"
>
    <div class="chat-preview" role="log" aria-label="{{ __('groups.detail.chat.recent_label') }}">
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
            <p class="group-dashboard__empty">{{ __('groups.detail.chat.empty') }}</p>
        @endforelse
    </div>

    <x-action-control
        label="{{ __('groups.detail.chat.open') }}"
        icon="message-circle"
        variant="paper"
        class="section-body"
        :href="route('messages.index')"
    />
</x-content-panel>
