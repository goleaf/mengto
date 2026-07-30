@props(['topic'])

<article class="forum-topic-card {{ $topic['is_urgent'] ? 'forum-topic-card--urgent' : '' }}">
    <div class="forum-topic-card__meta">
        <span class="forum-badge {{ $topic['is_urgent'] ? 'forum-badge--danger' : '' }}">
            @if ($topic['is_urgent'])
                <x-lucide-triangle-alert aria-hidden="true" />
            @endif
            {{ $topic['status_label'] }}
        </span>
        <span>{{ $topic['type_label'] }}</span>
        <span>{{ $topic['category_label'] }}</span>
        @if ($topic['location'])
            <span><x-lucide-map-pin aria-hidden="true" /> {{ $topic['location'] }}</span>
        @endif
    </div>

    <h2>
        <a href="{{ route('forum.topics.show', $topic['slug']) }}">{{ $topic['title'] }}</a>
    </h2>

    <p class="forum-topic-card__excerpt">{{ $topic['excerpt'] }}</p>

    @if ($topic['tags'] !== [])
        <div class="forum-topic-card__tags" aria-label="{{ __('ui.topic_tags_e3f08dc56f') }}">
            @forelse ($topic['tags'] as $tag)
                <span class="forum-topic-card__tag">{{ $tag }}</span>
            @empty
                <span class="sr-only">{{ __('ui.no_topic_tags_f441952188') }}</span>
            @endforelse
        </div>
    @endif

    <div class="forum-topic-card__facts" aria-label="{{ __('ui.topic_activity_e7e514dfe2') }}">
        <span><x-lucide-message-square aria-hidden="true" /> {{ trans_choice('presentation.answers_count', $topic['answers_count'], ['count' => $topic['answers_count']]) }}</span>
        <span><x-lucide-messages-square aria-hidden="true" /> {{ trans_choice('presentation.comments_count', $topic['comments_count'], ['count' => $topic['comments_count']]) }}</span>
        <span><x-lucide-thumbs-up aria-hidden="true" /> {{ __('presentation.helpful_count', ['count' => $topic['helpful_score']]) }}</span>
        <span><x-lucide-eye aria-hidden="true" /> {{ $topic['view_count'] }}</span>
        @if ($topic['has_expert_answer'])
            <span class="forum-badge"><x-lucide-badge-check aria-hidden="true" /> {{ __('ui.expert_reply_6da9cb7be8') }}</span>
        @endif
        @if ($topic['has_accepted_answer'])
            <span class="forum-badge"><x-lucide-circle-check-big aria-hidden="true" /> {{ __('ui.accepted_a00fb0c507') }}</span>
        @endif
    </div>

    <footer class="forum-topic-card__footer">
        <div class="forum-topic-card__author">
            <span class="forum-topic-card__avatar" aria-hidden="true">{{ $topic['author_initials'] }}</span>
            <span>
                <strong>{{ $topic['author_name'] }}</strong>
                <span>
                    {{ $topic['pet_name'] ? __('ui.with_b708724a23').' '.$topic['pet_name'].' / ' : '' }}{{ $topic['activity_label'] }}
                </span>
            </span>
        </div>

        <div class="forum-actions">
            <form method="POST" action="{{ route('forum.actions') }}">
                @csrf
                <input type="hidden" name="action" value="toggle-bookmark">
                <input type="hidden" name="topic_id" value="{{ $topic['id'] }}">
                <button type="submit" class="forum-button" aria-pressed="{{ $topic['bookmarked'] ? 'true' : 'false' }}">
                    <x-dynamic-component
                        :component="$topic['bookmarked'] ? 'lucide-bookmark-check' : 'lucide-bookmark'"
                        aria-hidden="true"
                    />
                    {{ $topic['bookmarked'] ? __('ui.saved_b5c120b316') : __('ui.save_1509f561f2') }}
                </button>
            </form>
            <a href="{{ route('forum.topics.show', $topic['slug']) }}" class="forum-button forum-button--primary">
                {{ __('ui.open_ed077f3d81') }}
                <x-lucide-arrow-right aria-hidden="true" />
            </a>
        </div>
    </footer>
</article>
