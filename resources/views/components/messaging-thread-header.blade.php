@props(['conversation', 'activeFilter'])

<header class="messaging-thread-header">
    <a
        href="{{ route('messages.index', ['filter' => $activeFilter]) }}"
        class="messaging-thread-header__back"
        aria-label="{{ __('ui.back_to_conversations_d456fc7566') }}"
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
        <span>{{ __('presentation.writing_as_person', ['purpose' => $conversation['purpose']]) }}</span>
    </div>

    <div class="messaging-thread-header__actions">
        <form method="POST" action="{{ route('messages.actions') }}">
            @csrf
            <input type="hidden" name="action" value="start-message-call">
            <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
            <input type="hidden" name="call_type" value="audio">
            <input type="hidden" name="recording_consent" value="no">
            <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
            <button type="submit" class="messaging-icon-button" title="{{ __('ui.start_audio_call_preflight_4ce1256cff') }}" @disabled($conversation['blocked'])>
                <x-lucide-phone class="icon icon--sm" aria-hidden="true" />
                <span class="sr-only">{{ __('ui.audio_call_3501f9a7a9') }}</span>
            </button>
        </form>

        <form method="POST" action="{{ route('messages.actions') }}">
            @csrf
            <input type="hidden" name="action" value="start-message-call">
            <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
            <input type="hidden" name="call_type" value="video">
            <input type="hidden" name="recording_consent" value="no">
            <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
            <button type="submit" class="messaging-icon-button" title="{{ __('ui.start_video_call_preflight_2109eee0a3') }}" @disabled($conversation['blocked'])>
                <x-lucide-video class="icon icon--sm" aria-hidden="true" />
                <span class="sr-only">{{ __('ui.video_call_7b79b4f672') }}</span>
            </button>
        </form>

        <a
            href="{{ route('messages.details', ['conversation' => $conversation['key']]) }}"
            class="messaging-icon-button"
            title="{{ __('ui.conversation_details_28b55e1258') }}"
        >
            <x-lucide-info class="icon icon--sm" aria-hidden="true" />
            <span class="sr-only">{{ __('ui.conversation_details_28b55e1258') }}</span>
        </a>
    </div>
</header>

<div class="messaging-thread-context">
    <x-lucide-paw-print class="icon icon--sm" aria-hidden="true" />
    <span>{{ __('presentation.context_pets', ['pets' => implode(', ', $conversation['pet_names'])]) }}</span>
    <strong>{{ $conversation['privacy'] }}</strong>
</div>
