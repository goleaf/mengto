@props(['messages', 'conversation'])

<div class="messaging-messages" role="log" aria-live="polite" aria-label="{{ __('ui.conversation_messages_f5f8903fca') }}">
    <div class="messaging-date-divider">
        <span></span>
        <time datetime="2026-07-30">{{ __('ui.today_2b065c7c9c') }}</time>
        <span></span>
    </div>

    @forelse ($messages as $message)
        <x-messaging-message :message="$message" :conversation="$conversation" />
    @empty
        <div class="messaging-messages__empty">
            <x-ui-icon name="message-circle-dashed" />
            <strong>{{ __('ui.no_matching_messages_bf3cda4412') }}</strong>
            <span>{{ __('ui.try_another_word_or_clear_message_search_a3215a2d1d') }}</span>
        </div>
    @endforelse
</div>
