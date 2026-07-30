@props(['messages', 'conversation'])

<div class="messaging-messages" role="log" aria-live="polite" aria-label="Conversation messages">
    <div class="messaging-date-divider">
        <span></span>
        <time datetime="2026-07-30">Today</time>
        <span></span>
    </div>

    @forelse ($messages as $message)
        <x-object.messaging-message :message="$message" :conversation="$conversation" />
    @empty
        <div class="messaging-messages__empty">
            <x-lucide-message-circle-dashed class="icon" aria-hidden="true" />
            <strong>No matching messages</strong>
            <span>Try another word or clear message search.</span>
        </div>
    @endforelse
</div>
