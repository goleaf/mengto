@props(['topic'])

<article class="forum-topic-card {{ $topic['is_urgent'] ? 'forum-topic-card--urgent' : '' }}">
    <div class="forum-topic-card__meta">
        <span class="forum-badge {{ $topic['is_urgent'] ? 'forum-badge--danger' : '' }}">
            @if ($topic['is_urgent'])
                <x-ui-icon name="triangle-alert" />
            @endif
            {{ $topic['status_label'] }}
        </span>
        <span>{{ $topic['type_label'] }}</span>
        <span>{{ $topic['category_label'] }}</span>
        @if ($topic['location'])
            <span><x-ui-icon name="map-pin" /> {{ $topic['location'] }}</span>
        @endif
    </div>

    <h2>
        <a href="{{ route('forum.topics.show', $topic['slug']) }}">{{ $topic['title'] }}</a>
    </h2>

    <p class="forum-topic-card__excerpt">{{ $topic['excerpt'] }}</p>

    @if ($topic['tags'] !== [])
        <div class="forum-topic-card__tags" aria-label="{{ __('ui.topic_tags') }}">
            @forelse ($topic['tags'] as $tag)
                <span class="forum-topic-card__tag">{{ $tag }}</span>
            @empty
                <span class="sr-only">{{ __('ui.no_topic_tags') }}</span>
            @endforelse
        </div>
    @endif

    <div class="forum-topic-card__facts" aria-label="{{ __('ui.topic_activity') }}">
        <span><x-ui-icon name="message-square" /> {{ trans_choice('presentation.answers_count', $topic['answers_count'], ['count' => $topic['answers_count']]) }}</span>
        <span><x-ui-icon name="messages-square" /> {{ trans_choice('presentation.comments_count', $topic['comments_count'], ['count' => $topic['comments_count']]) }}</span>
        <span><x-ui-icon name="thumbs-up" /> {{ __('presentation.helpful_count', ['count' => $topic['helpful_score']]) }}</span>
        <span><x-ui-icon name="eye" /> {{ $topic['view_count'] }}</span>
        @if ($topic['has_expert_answer'])
            <span class="forum-badge"><x-ui-icon name="badge-check" /> {{ __('ui.expert_reply') }}</span>
        @endif
        @if ($topic['has_accepted_answer'])
            <span class="forum-badge"><x-ui-icon name="circle-check-big" /> {{ __('ui.accepted') }}</span>
        @endif
    </div>

    <footer class="forum-topic-card__footer">
        <div class="forum-topic-card__author">
            <span class="forum-topic-card__avatar" aria-hidden="true">{{ $topic['author_initials'] }}</span>
            <span>
                <strong>{{ $topic['author_name'] }}</strong>
                <span>
                    {{ $topic['pet_name'] ? __('ui.with').' '.$topic['pet_name'].' / ' : '' }}{{ $topic['activity_label'] }}
                </span>
            </span>
        </div>

        <div class="forum-actions">
            <form method="POST" action="{{ route('forum.actions') }}">
                @csrf
                <input type="hidden" name="action" value="toggle-bookmark">
                <input type="hidden" name="topic_id" value="{{ $topic['id'] }}">
                <button type="submit" class="forum-button" aria-pressed="{{ $topic['bookmarked'] ? 'true' : 'false' }}">
                    <x-ui-icon :name="$topic['bookmarked'] ? 'bookmark-check' : 'bookmark'" />
                    {{ $topic['bookmarked'] ? __('ui.saved') : __('ui.save') }}
                </button>
            </form>
            <a href="{{ route('forum.topics.show', $topic['slug']) }}" class="forum-button forum-button--primary">
                {{ __('ui.open') }}
                <x-ui-icon name="arrow-right" />
            </a>
        </div>
    </footer>
</article>
