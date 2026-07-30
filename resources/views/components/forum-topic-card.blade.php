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
        <div class="forum-topic-card__tags" aria-label="Topic tags">
            @forelse ($topic['tags'] as $tag)
                <span class="forum-topic-card__tag">{{ $tag }}</span>
            @empty
                <span class="sr-only">No topic tags.</span>
            @endforelse
        </div>
    @endif

    <div class="forum-topic-card__facts" aria-label="Topic activity">
        <span><x-lucide-message-square aria-hidden="true" /> {{ $topic['answers_count'] }} answers</span>
        <span><x-lucide-messages-square aria-hidden="true" /> {{ $topic['comments_count'] }} comments</span>
        <span><x-lucide-thumbs-up aria-hidden="true" /> {{ $topic['helpful_score'] }} helpful</span>
        <span><x-lucide-eye aria-hidden="true" /> {{ $topic['view_count'] }}</span>
        @if ($topic['has_expert_answer'])
            <span class="forum-badge"><x-lucide-badge-check aria-hidden="true" /> Expert reply</span>
        @endif
        @if ($topic['has_accepted_answer'])
            <span class="forum-badge"><x-lucide-circle-check-big aria-hidden="true" /> Accepted</span>
        @endif
    </div>

    <footer class="forum-topic-card__footer">
        <div class="forum-topic-card__author">
            <span class="forum-topic-card__avatar" aria-hidden="true">{{ $topic['author_initials'] }}</span>
            <span>
                <strong>{{ $topic['author_name'] }}</strong>
                <span>
                    {{ $topic['pet_name'] ? 'With '.$topic['pet_name'].' / ' : '' }}{{ $topic['activity_label'] }}
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
                    {{ $topic['bookmarked'] ? 'Saved' : 'Save' }}
                </button>
            </form>
            <a href="{{ route('forum.topics.show', $topic['slug']) }}" class="forum-button forum-button--primary">
                Open
                <x-lucide-arrow-right aria-hidden="true" />
            </a>
        </div>
    </footer>
</article>
