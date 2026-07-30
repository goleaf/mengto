<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <header class="forum-header">
            <div class="forum-header__copy">
                <p class="forum-header__eyebrow">Reviewed library</p>
                <h1>Knowledge with a revision date.</h1>
                <p>Editorial guides, checklists, and FAQ material shaped from useful discussions and named sources.</p>
            </div>
            <div class="forum-header__actions">
                <a href="{{ route('forum.index') }}" class="forum-button">
                    <x-lucide-messages-square aria-hidden="true" />
                    Forum
                </a>
                <a href="{{ route('forum.topics.create') }}" class="forum-button forum-button--primary">
                    <x-lucide-circle-help aria-hidden="true" />
                    Ask a question
                </a>
            </div>
        </header>

        <form method="GET" action="{{ route('knowledge.index') }}" class="forum-search">
            <label>
                <span class="sr-only">Search knowledge base</span>
                <x-lucide-search aria-hidden="true" />
                <input name="q" value="{{ $filters['q'] }}" placeholder="Search guides, checklists, and sources">
            </label>
            <select name="category" aria-label="Knowledge category">
                <option value="all">All categories</option>
                @forelse ($categories as $key => $label)
                    <option value="{{ $key }}" @selected($filters['category'] === $key)>{{ $label }}</option>
                @empty
                    <option value="all">All categories</option>
                @endforelse
            </select>
            <select name="type" aria-label="Knowledge format">
                @forelse ($types as $key => $label)
                    <option value="{{ $key }}" @selected($filters['type'] === $key)>{{ $label }}</option>
                @empty
                    <option value="all">All formats</option>
                @endforelse
            </select>
            <button type="submit" class="forum-button forum-button--primary">
                <x-lucide-search aria-hidden="true" />
                Search
            </button>
        </form>

        <section class="knowledge-grid" aria-label="Knowledge articles">
            @forelse ($articles as $article)
                <x-knowledge-article-card :article="$article" />
            @empty
                <div class="forum-form">
                    <h2>No reviewed article matches these filters</h2>
                    <p>Try a broader search or ask a focused forum question.</p>
                </div>
            @endforelse
        </section>

        <div>{{ $articles->links() }}</div>
    </div>
</x-app-shell>
