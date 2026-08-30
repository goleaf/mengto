<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <x-page-header
            :eyebrow="__('ui.reviewed_library')"
            :title="__('ui.knowledge_with_a_revision_date')"
            :description="__('ui.editorial_guides_checklists_and_faq_material_shaped_from_useful_discussions_and_named_sources')"
            heading-id="knowledge-directory-heading"
            data-section="knowledge-directory-header"
        >
            <x-slot:actions>
                <x-action-control
                    :label="__('ui.forum')"
                    icon="messages-square"
                    :href="route('forum.index')"
                    variant="paper"
                    size="regular"
                />
                <x-action-control
                    :label="__('ui.ask_a_question')"
                    icon="circle-help"
                    :href="route('forum.topics.create')"
                    variant="paper"
                    size="regular"
                />
                @if ($can_create)
                    <x-action-control
                        :label="__('knowledge.actions.create_guide')"
                        icon="file-plus-2"
                        :href="route('knowledge.guides.create')"
                        size="regular"
                    />
                @endif
            </x-slot:actions>
        </x-page-header>

        <form method="GET" action="{{ route('knowledge.index') }}" class="forum-search">
            <label>
                <span class="sr-only">{{ __('ui.search_knowledge_base') }}</span>
                <x-ui-icon name="search" />
                <input name="q" value="{{ $filters['q'] }}" placeholder="{{ __('ui.search_guides_checklists_and_sources') }}">
            </label>
            <select name="category" aria-label="{{ __('ui.knowledge_category') }}">
                <option value="all">{{ __('ui.all_categories') }}</option>
                @forelse ($categories as $key => $label)
                    <option value="{{ $key }}" @selected($filters['category'] === $key)>{{ $label }}</option>
                @empty
                    <option value="all">{{ __('ui.all_categories') }}</option>
                @endforelse
            </select>
            <select name="type" aria-label="{{ __('ui.knowledge_format') }}">
                @forelse ($types as $key => $label)
                    <option value="{{ $key }}" @selected($filters['type'] === $key)>{{ $label }}</option>
                @empty
                    <option value="all">{{ __('ui.all_formats') }}</option>
                @endforelse
            </select>
            <button type="submit" class="forum-button forum-button--primary">
                <x-ui-icon name="search" />
                {{ __('ui.search') }}
            </button>
        </form>

        <section class="knowledge-grid" aria-label="{{ __('ui.knowledge_articles') }}">
            @forelse ($articles as $article)
                <x-knowledge-article-card :article="$article" />
            @empty
                <div class="forum-form">
                    <h2>{{ __('ui.no_reviewed_article_matches_these_filters') }}</h2>
                    <p>{{ __('ui.try_a_broader_search_or_ask_a_focused_forum_question') }}</p>
                </div>
            @endforelse
        </section>

        <div>{{ $articles->links() }}</div>
    </div>
</x-app-shell>
