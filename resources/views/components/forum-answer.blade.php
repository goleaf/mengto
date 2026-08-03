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
                @if ($answer['expert_profile'])
                    <strong><a href="{{ route('experts.show', $answer['expert_profile']['slug']) }}">{{ $answer['author_name'] }}</a></strong>
                @else
                    <strong>{{ $answer['author_name'] }}</strong>
                @endif
                <span>{{ $answer['author_role'] }} / {{ $answer['created_label'] }}</span>
            </span>
        </div>
        <div class="forum-topic-card__tags">
            @if ($answer['is_accepted'])
                <span class="forum-badge"><x-ui-icon name="circle-check-big" /> {{ __('ui.accepted_answer_205ea1c1e2') }}</span>
            @endif
            @if ($answer['is_verified_expert'])
                <span class="forum-badge">
                    <x-ui-icon name="badge-check" />
                    {{ ($answer['expert_profile']['qualification_verified'] ?? false) ? __('ui.qualification_verified_bfd453f9ac') : $answer['expertise'] }}
                </span>
                @if ($answer['expert_profile'] && $answer['expert_profile']['profile_status'] !== 'Published')
                    <span class="forum-badge forum-badge--sun">
                        {{ __('presentation.profile_status', ['status' => $answer['expert_profile']['profile_status']]) }}
                    </span>
                @endif
            @else
                <span class="forum-badge forum-badge--neutral">{{ $answer['experience_label'] }}</span>
            @endif
            @if ($answer['needs_source'])
                <span class="forum-badge forum-badge--sun"><x-ui-icon name="link" /> {{ __('ui.source_requested_dd87a1a3ea') }}</span>
            @endif
        </div>
    </header>

    <div class="forum-answer__body">{{ $answer['body'] }}</div>

    @if ($answer['sources'] !== [])
        <div class="forum-answer__sources">
            <strong>{{ __('ui.sources_caf85b0888') }}</strong>
            @forelse ($answer['sources'] as $source)
                <a href="{{ $source }}" target="_blank" rel="noopener noreferrer">{{ $source }}</a>
            @empty
                <span>{{ __('ui.no_sources_listed_5a688e193a') }}</span>
            @endforelse
        </div>
    @endif

    @if ($answer['comments'] !== [])
        <div class="forum-comments" aria-label="{{ __('ui.comments_on_this_answer_6d23397bb0') }}">
            @forelse ($answer['comments'] as $comment)
                <div class="forum-comments__item {{ $comment['parent_id'] ? 'forum-comments__item--reply' : '' }}">
                    <strong>{{ $comment['author_name'] }}</strong>
                    <span> / {{ $comment['created_label'] }}</span>
                    @if ($comment['is_pinned'])
                        <span class="forum-badge forum-badge--sun">{{ __('ui.pinned_clarification_f22d156e4e') }}</span>
                    @endif
                    <p>{{ $comment['body'] }}</p>
                </div>
            @empty
                <span>{{ __('ui.no_comments_71153e6b23') }}</span>
            @endforelse
        </div>
    @endif

    <footer class="forum-answer__footer">
        <div class="forum-actions">
            <form method="POST" action="{{ route('forum.actions') }}">
                @csrf
                <input type="hidden" name="action" value="vote-answer">
                <input type="hidden" name="answer_id" value="{{ $answer['id'] }}">
                <input type="hidden" name="value" value="helpful">
                <button type="submit" class="forum-button" aria-pressed="{{ $answer['voted'] === 'helpful' ? 'true' : 'false' }}">
                    <x-ui-icon name="thumbs-up" />
                    {{ __('presentation.helpful_count', ['count' => $answer['helpful_count']]) }}
                </button>
            </form>

            @if ($canManage && ! $answer['is_accepted'])
                <form method="POST" action="{{ route('forum.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="accept-answer">
                    <input type="hidden" name="answer_id" value="{{ $answer['id'] }}">
                    <button type="submit" class="forum-button">
                        <x-ui-icon name="circle-check-big" />
                        {{ __('ui.accept_89713b9c9c') }}
                    </button>
                </form>
            @endif

            <details>
                <summary class="forum-button">
                    <x-ui-icon name="flag" />
                    {{ __('ui.report_b6ce788d97') }}
                </summary>
                <form method="POST" action="{{ route('forum.actions') }}" class="forum-form mt-2">
                    @csrf
                    <input type="hidden" name="action" value="report-answer">
                    <input type="hidden" name="answer_id" value="{{ $answer['id'] }}">
                    <label class="forum-form__field">
                        <span>{{ __('ui.reason_f81ab834de') }}</span>
                        <select name="reason" required>
                            <option value="dangerous-advice">{{ __('ui.dangerous_advice_df40777716') }}</option>
                            <option value="misinformation">{{ __('ui.misinformation_34d52e35bd') }}</option>
                            <option value="spam">{{ __('ui.spam_94a9eac404') }}</option>
                            <option value="harassment">{{ __('ui.harassment_98a7655d02') }}</option>
                            <option value="other">{{ __('ui.other_f97e9da0e3') }}</option>
                        </select>
                    </label>
                    <label class="forum-form__check">
                        <input type="checkbox" name="truthfulness_confirmed" value="1" required>
                        <span>{{ __('forum_moderation.forms.truthfulness') }}</span>
                    </label>
                    <label class="forum-form__check">
                        <input type="checkbox" name="immediate_safety" value="1">
                        <span>{{ __('forum_moderation.forms.immediate_safety') }}</span>
                    </label>
                    <label class="forum-form__check">
                        <input type="checkbox" name="block_user" value="1">
                        <span>{{ __('forum_moderation.forms.block_user') }}</span>
                    </label>
                    <button type="submit" class="forum-button forum-button--danger">
                        <x-ui-icon name="send" />
                        {{ __('ui.send_report_a44d353113') }}
                    </button>
                </form>
            </details>
        </div>

        @if (! $topic['is_locked'])
            <details>
                <summary class="forum-button">
                    <x-ui-icon name="message-circle-plus" />
                    {{ __('ui.comment_44f5e3fbec') }}
                </summary>
                <form method="POST" action="{{ route('forum.comments.store', $topic['slug']) }}" class="forum-form mt-2">
                    @csrf
                    <input type="hidden" name="answer_id" value="{{ $answer['id'] }}">
                    <label class="forum-form__field">
                        <span>{{ __('ui.clarification_or_focused_comment_557b219d7a') }}</span>
                        <textarea name="body" minlength="2" maxlength="1500" required></textarea>
                    </label>
                    <button type="submit" class="forum-button forum-button--primary">
                        <x-ui-icon name="send" />
                        {{ __('ui.add_comment_c4d891e9a8') }}
                    </button>
                </form>
            </details>
        @endif
    </footer>
</article>
