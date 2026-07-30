<article
    id="message-{{ $message['id'] }}"
    @class([
        'messaging-message',
        'messaging-message--mine' => $message['mine'],
        'messaging-message--structured' => $structured,
        'messaging-message--warning' => $message['type'] === 'warning',
        'messaging-message--system' => $message['type'] === 'system',
    ])
>
    <header>
        <strong>{{ $message['sender'] }}</strong>
        @if ($message['type'] === 'professional')
            <x-lucide-badge-check class="icon icon--sm" aria-label="Verified professional answer" />
        @endif
        <time datetime="{{ $message['datetime'] }}">{{ $message['time'] }}</time>
    </header>

    <div class="messaging-message__bubble">
        @if ($message['reply'])
            <blockquote>
                <x-lucide-reply class="icon icon--xs" aria-hidden="true" />
                {{ $message['reply'] }}
            </blockquote>
        @endif

        @if ($structured)
            <div class="messaging-message__media">
                <span><x-dynamic-component :component="'lucide-'.$icon" class="icon" aria-hidden="true" /></span>
                <div>
                    <small>{{ $typeLabel }}</small>
                    <strong>{{ $message['body'] }}</strong>
                    @if ($message['meta'])
                        <p>{{ $message['meta'] }}</p>
                    @endif
                </div>
                @if ($message['type'] === 'audio')
                    <button type="button" class="messaging-audio" data-audio-toggle aria-label="Play audio message" aria-pressed="false">
                        <x-lucide-play class="icon icon--sm" aria-hidden="true" />
                        <span aria-hidden="true">
                            @for ($bar = 0; $bar < 12; $bar++)
                                <i style="--bar: {{ ($bar % 5) + 1 }}"></i>
                            @endfor
                        </span>
                    </button>
                @endif
            </div>
        @else
            <p>{{ $message['body'] }}</p>
        @endif
    </div>

    <footer>
        @if ($message['edited'])
            <span>Edited</span>
        @endif
        @if ($message['pinned'] ?? false)
            <span><x-lucide-pin class="icon icon--xs" aria-hidden="true" /> Pinned</span>
        @endif
        @if ($message['bookmarked'] ?? false)
            <span><x-lucide-bookmark class="icon icon--xs" aria-hidden="true" /> Saved privately</span>
        @endif
        @if ($message['reaction'] ?? null)
            <span><x-lucide-smile-plus class="icon icon--xs" aria-hidden="true" /> {{ $reactionLabel }}</span>
        @endif
        <span>{{ $message['status'] }}</span>

        <details class="messaging-message-menu">
            <summary aria-label="Message actions"><x-lucide-ellipsis class="icon icon--sm" aria-hidden="true" /></summary>
            <div>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="react-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <input type="hidden" name="reaction" value="thanks">
                    <button type="submit"><x-lucide-smile-plus class="icon icon--sm" /> Thanks</button>
                </form>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="pin-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <button type="submit"><x-lucide-pin class="icon icon--sm" /> Pin</button>
                </form>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="bookmark-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <button type="submit"><x-lucide-bookmark class="icon icon--sm" /> Save</button>
                </form>
                <button
                    type="button"
                    data-message-reply-trigger
                    data-message-reply-text="{{ $replyText }}"
                >
                    <x-lucide-reply class="icon icon--sm" /> Reply
                </button>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="delete-message-self">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <button type="submit"><x-lucide-eye-off class="icon icon--sm" /> Delete for me</button>
                </form>
                @if ($message['mine'])
                    <form method="POST" action="{{ route('messages.actions') }}">
                        @csrf
                        <input type="hidden" name="action" value="delete-message-everyone">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="message" value="{{ $message['id'] }}">
                        <button type="submit"><x-lucide-trash-2 class="icon icon--sm" /> Delete for all</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="report-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <input type="hidden" name="report_reason" value="other">
                    <input type="hidden" name="body" value="Review this message with its surrounding context.">
                    <button type="submit"><x-lucide-flag class="icon icon--sm" /> Report</button>
                </form>
                @if ($editable)
                    <form method="POST" action="{{ route('messages.actions') }}" class="messaging-message-menu__edit">
                        @csrf
                        <input type="hidden" name="action" value="edit-message">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="message" value="{{ $message['id'] }}">
                        <label>
                            <span>Edit message</span>
                            <input type="text" name="body" value="{{ $message['body'] }}" maxlength="4000" required>
                        </label>
                        <button type="submit"><x-lucide-check class="icon icon--sm" /> Save edit</button>
                    </form>
                @endif
            </div>
        </details>
    </footer>
</article>
