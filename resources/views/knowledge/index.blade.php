<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <header class="forum-header">
            <div class="forum-header__copy">
                <p class="forum-header__eyebrow">{{ __('ui.reviewed_library_11f886e1e8') }}</p>
                <h1>{{ __('ui.knowledge_with_a_revision_date_70ac500514') }}</h1>
                <p>{{ __('ui.editorial_guides_checklists_and_faq_material_shaped_from_6e4fb5641d') }}</p>
            </div>
            <div class="forum-header__actions">
                <a href="{{ route('forum.index') }}" class="forum-button">
                    <x-lucide-messages-square aria-hidden="true" />
                    {{ __('ui.forum_4da7bd42ab') }}
                </a>
                <a href="{{ route('forum.topics.create') }}" class="forum-button forum-button--primary">
                    <x-lucide-circle-help aria-hidden="true" />
                    {{ __('ui.ask_a_question_3a533d7ef8') }}
                </a>
            </div>
        </header>

        <form method="GET" action="{{ route('knowledge.index') }}" class="forum-search">
            <label>
                <span class="sr-only">{{ __('ui.search_knowledge_base_74e433bf7d') }}</span>
                <x-lucide-search aria-hidden="true" />
                <input name="q" value="{{ $filters['q'] }}" placeholder="{{ __('ui.search_guides_checklists_and_sources_b85c3ad05e') }}">
            </label>
            <select name="category" aria-label="{{ __('ui.knowledge_category_efc27adf3a') }}">
                <option value="all">{{ __('ui.all_categories_9d5097a837') }}</option>
                @forelse ($categories as $key => $label)
                    <option value="{{ $key }}" @selected($filters['category'] === $key)>{{ $label }}</option>
                @empty
                    <option value="all">{{ __('ui.all_categories_9d5097a837') }}</option>
                @endforelse
            </select>
            <select name="type" aria-label="{{ __('ui.knowledge_format_775735d608') }}">
                @forelse ($types as $key => $label)
                    <option value="{{ $key }}" @selected($filters['type'] === $key)>{{ $label }}</option>
                @empty
                    <option value="all">{{ __('ui.all_formats_8a01c6b5b4') }}</option>
                @endforelse
            </select>
            <button type="submit" class="forum-button forum-button--primary">
                <x-lucide-search aria-hidden="true" />
                {{ __('ui.search_49c266baaa') }}
            </button>
        </form>

        <section class="knowledge-grid" aria-label="{{ __('ui.knowledge_articles_ad447a8eef') }}">
            @forelse ($articles as $article)
                <x-knowledge-article-card :article="$article" />
            @empty
                <div class="forum-form">
                    <h2>{{ __('ui.no_reviewed_article_matches_these_filters_269c759829') }}</h2>
                    <p>{{ __('ui.try_a_broader_search_or_ask_a_focused_92e1a7d055') }}</p>
                </div>
            @endforelse
        </section>

        <div>{{ $articles->links() }}</div>
    </div>
</x-app-shell>
