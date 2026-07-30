@props(['conversation', 'activeFilter'])

<header class="messaging-thread-header">
    <a
        href="{{ route('pet-social.messages.index', ['filter' => $activeFilter]) }}"
        class="messaging-thread-header__back"
        aria-label="Back to conversations"
    >
        <x-lucide-arrow-left class="icon" aria-hidden="true" />
    </a>

    <img src="{{ $conversation['avatar'] }}" alt="{{ $conversation['avatar_alt'] }}" width="48" height="48">

    <div class="messaging-thread-header__identity">
        <span class="messaging-thread-header__title">
            <h2>{{ $conversation['name'] }}</h2>
            @if ($conversation['professional'])
                <x-lucide-badge-check class="icon icon--sm" aria-label="{{ $conversation['verified'] }}" />
            @endif
        </span>
        <p>{{ $conversation['handle'] }} · {{ $conversation['presence'] }}</p>
        <span>{{ $conversation['purpose'] }} · Writing as a person</span>
    </div>

    <div class="messaging-thread-header__actions">
        <form method="POST" action="{{ route('pet-social.messages.actions') }}">
            @csrf
            <input type="hidden" name="action" value="start-message-call">
            <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
            <input type="hidden" name="call_type" value="audio">
            <input type="hidden" name="recording_consent" value="no">
            <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
            <button type="submit" class="messaging-icon-button" title="Start audio call preflight" @disabled($conversation['blocked'])>
                <x-lucide-phone class="icon icon--sm" aria-hidden="true" />
                <span class="sr-only">Audio call</span>
            </button>
        </form>

        <form method="POST" action="{{ route('pet-social.messages.actions') }}">
            @csrf
            <input type="hidden" name="action" value="start-message-call">
            <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
            <input type="hidden" name="call_type" value="video">
            <input type="hidden" name="recording_consent" value="no">
            <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
            <button type="submit" class="messaging-icon-button" title="Start video call preflight" @disabled($conversation['blocked'])>
                <x-lucide-video class="icon icon--sm" aria-hidden="true" />
                <span class="sr-only">Video call</span>
            </button>
        </form>

        <a
            href="{{ route('pet-social.messages.details', ['conversation' => $conversation['key']]) }}"
            class="messaging-icon-button"
            title="Conversation details"
        >
            <x-lucide-info class="icon icon--sm" aria-hidden="true" />
            <span class="sr-only">Conversation details</span>
        </a>
    </div>
</header>

<div class="messaging-thread-context">
    <x-lucide-paw-print class="icon icon--sm" aria-hidden="true" />
    <span>Context: {{ implode(', ', $conversation['pet_names']) }}</span>
    <strong>{{ $conversation['privacy'] }}</strong>
</div>
