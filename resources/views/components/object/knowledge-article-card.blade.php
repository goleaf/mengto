@props(['article'])

<article class="knowledge-card">
    <div>
        <span class="forum-badge {{ $article['is_outdated'] ? 'forum-badge--danger' : '' }}">
            <x-dynamic-component
                :component="$article['is_outdated'] ? 'lucide-history' : 'lucide-book-open-check'"
                aria-hidden="true"
            />
            {{ $article['type_label'] }}
        </span>
    </div>
    <h3>
        <a href="{{ route('knowledge.articles.show', $article['slug']) }}">{{ $article['title'] }}</a>
    </h3>
    <p>{{ $article['summary'] }}</p>
    <div class="knowledge-card__meta">
        <span>{{ $article['category_label'] ?? $article['category'] }}</span>
        <span>/</span>
        <span>{{ $article['difficulty_label'] }}</span>
        <span>/</span>
        <span>{{ $article['reviewed_label'] }}</span>
    </div>
</article>
