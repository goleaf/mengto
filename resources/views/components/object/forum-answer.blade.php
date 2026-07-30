@props([
    'answer',
    'topic',
    'canManage' => false,
])

<article
    id="answer-{{ $answer['id'] }}"
    class="forum-answer {{ $answer['is_accepted'] ? 'forum-answer--accepted' : '' }}"
>
    <header class="forum-answer__header">
        <div class="forum-topic-card__author">
            <span class="forum-topic-card__avatar" aria-hidden="true">{{ $answer['author_initials'] }}</span>
            <span>
                <strong>{{ $answer['author_name'] }}</strong>
                <span>{{ $answer['author_role'] }} / {{ $answer['created_label'] }}</span>
            </span>
        </div>
        <div class="forum-topic-card__tags">
            @if ($answer['is_accepted'])
                <span class="forum-badge"><x-lucide-circle-check-big aria-hidden="true" /> Accepted answer</span>
            @endif
            @if ($answer['is_verified_expert'])
                <span class="forum-badge">
                    <x-lucide-badge-check aria-hidden="true" />
                    {{ $answer['expertise'] }}
                </span>
            @else
                <span class="forum-badge forum-badge--neutral">{{ $answer['experience_label'] }}</span>
            @endif
            @if ($answer['needs_source'])
                <span class="forum-badge forum-badge--sun"><x-lucide-link aria-hidden="true" /> Source requested</span>
            @endif
        </div>
    </header>

    <div class="forum-answer__body">{{ $answer['body'] }}</div>

    @if ($answer['sources'] !== [])
        <div class="forum-answer__sources">
            <strong>Sources</strong>
            @forelse ($answer['sources'] as $source)
                <a href="{{ $source }}" target="_blank" rel="noopener noreferrer">{{ $source }}</a>
            @empty
                <span>No sources listed.</span>
            @endforelse
        </div>
    @endif

    @if ($answer['comments'] !== [])
        <div class="forum-comments" aria-label="Comments on this answer">
            @forelse ($answer['comments'] as $comment)
                <div class="forum-comments__item {{ $comment['parent_id'] ? 'forum-comments__item--reply' : '' }}">
                    <strong>{{ $comment['author_name'] }}</strong>
                    <span> / {{ $comment['created_label'] }}</span>
                    @if ($comment['is_pinned'])
                        <span class="forum-badge forum-badge--sun">Pinned clarification</span>
                    @endif
                    <p>{{ $comment['body'] }}</p>
                </div>
            @empty
                <span>No comments.</span>
            @endforelse
        </div>
    @endif

    <footer class="forum-answer__footer">
        <div class="forum-actions">
            <form method="POST" action="{{ route('pet-social.forum.actions') }}">
                @csrf
                <input type="hidden" name="action" value="vote-answer">
                <input type="hidden" name="answer_id" value="{{ $answer['id'] }}">
                <input type="hidden" name="value" value="helpful">
                <button type="submit" class="forum-button" aria-pressed="{{ $answer['voted'] === 'helpful' ? 'true' : 'false' }}">
                    <x-lucide-thumbs-up aria-hidden="true" />
                    Helpful {{ $answer['helpful_count'] }}
                </button>
            </form>

            @if ($canManage && ! $answer['is_accepted'])
                <form method="POST" action="{{ route('pet-social.forum.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="accept-answer">
                    <input type="hidden" name="answer_id" value="{{ $answer['id'] }}">
                    <button type="submit" class="forum-button">
                        <x-lucide-circle-check-big aria-hidden="true" />
                        Accept
                    </button>
                </form>
            @endif

            <details>
                <summary class="forum-button">
                    <x-lucide-flag aria-hidden="true" />
                    Report
                </summary>
                <form method="POST" action="{{ route('pet-social.forum.actions') }}" class="forum-form mt-2">
                    @csrf
                    <input type="hidden" name="action" value="report-answer">
                    <input type="hidden" name="answer_id" value="{{ $answer['id'] }}">
                    <label class="forum-form__field">
                        <span>Reason</span>
                        <select name="reason" required>
                            <option value="dangerous-advice">Dangerous advice</option>
                            <option value="misinformation">Misinformation</option>
                            <option value="spam">Spam</option>
                            <option value="harassment">Harassment</option>
                            <option value="other">Other</option>
                        </select>
                    </label>
                    <button type="submit" class="forum-button forum-button--danger">
                        <x-lucide-send aria-hidden="true" />
                        Send report
                    </button>
                </form>
            </details>
        </div>

        @if (! $topic['is_locked'])
            <details>
                <summary class="forum-button">
                    <x-lucide-message-circle-plus aria-hidden="true" />
                    Comment
                </summary>
                <form method="POST" action="{{ route('pet-social.forum.comments.store', $topic['slug']) }}" class="forum-form mt-2">
                    @csrf
                    <input type="hidden" name="answer_id" value="{{ $answer['id'] }}">
                    <label class="forum-form__field">
                        <span>Clarification or focused comment</span>
                        <textarea name="body" minlength="2" maxlength="1500" required></textarea>
                    </label>
                    <button type="submit" class="forum-button forum-button--primary">
                        <x-lucide-send aria-hidden="true" />
                        Add comment
                    </button>
                </form>
            </details>
        @endif
    </footer>
</article>
