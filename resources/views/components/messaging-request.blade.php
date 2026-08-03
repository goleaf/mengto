@props(['conversation'])

<section class="messaging-request" aria-labelledby="message-request-title">
    <img src="{{ $conversation['avatar'] }}" alt="{{ $conversation['avatar_alt'] }}" width="88" height="88">
    <p>{{ __('ui.new_message_request_fccaa57776') }}</p>
    <h2 id="message-request-title">{{ $conversation['name'] }}</h2>
    <span>{{ $conversation['verified'] }} · {{ $conversation['pet'] }}</span>
    <blockquote>{{ $conversation['preview'] }}</blockquote>

    <div class="messaging-request__context">
        <span><x-ui-icon name="paw-print" size="sm" /> {{ __('ui.linked_pet_context_only_72358e44e9') }}</span>
        <span><x-ui-icon name="eye-off" size="sm" /> {{ __('ui.read_status_hidden_118784a0e4') }}</span>
        <span><x-ui-icon name="paperclip" size="sm" /> {{ __('ui.media_blocked_until_accepted_7506bc5901') }}</span>
    </div>

    <div class="messaging-request__actions">
        @foreach ([
            ['action' => 'accept-message-request', 'label' => __('ui.accept_89713b9c9c'), 'icon' => 'check', 'class' => 'action--primary'],
            ['action' => 'decline-message-request', 'label' => __('ui.decline_a2d285b352'), 'icon' => 'x', 'class' => 'action--paper'],
            ['action' => 'block-conversation', 'label' => __('ui.block_211d0bb8cf'), 'icon' => 'ban', 'class' => 'action--danger'],
        ] as $requestAction)
            <form method="POST" action="{{ route('messages.actions') }}">
                @csrf
                <input type="hidden" name="action" value="{{ $requestAction['action'] }}">
                <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                <button type="submit" class="action {{ $requestAction['class'] }} action--regular">
                    <x-ui-icon size="sm" :name="$requestAction['icon']" />
                    <span>{{ $requestAction['label'] }}</span>
                </button>
            </form>
        @endforeach
    </div>

    <p class="messaging-request__note">{{ __('ui.accepting_allows_replies_and_call_requests_it_never_ee55223e5f') }}</p>
</section>
