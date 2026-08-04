@props(['messages', 'conversation'])

<div class="messaging-messages" role="log" aria-live="polite" aria-label="{{ __('messaging.thread.messages_label') }}" data-messaging-message-list>
    <div class="messaging-date-divider">
        <span></span>
        <time datetime="2026-07-30">{{ __('messaging.relative.today') }}</time>
        <span></span>
    </div>

    @forelse ($messages as $message)
        <x-messaging-message :message="$message" :conversation="$conversation" />
    @empty
        <div class="messaging-messages__empty">
            <x-ui-icon name="message-circle-dashed" />
            <strong>{{ __('messaging.thread.empty_title') }}</strong>
            <span>{{ __('messaging.thread.empty_description') }}</span>
        </div>
    @endforelse
</div>
