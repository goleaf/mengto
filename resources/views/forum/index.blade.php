<x-app-shell :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <x-page-header
            :eyebrow="__('ui.community_knowledge')"
            :title="__('ui.ask_well_find_what_lasts')"
            :description="__('ui.questions_field_notes_expert_context_and_practical_guides_that_remain_useful_after_the_feed_moves_on')"
            heading-id="forum-directory-heading"
            data-section="forum-directory-header"
        >
            <x-slot:actions>
                <x-action-control :label="__('ui.knowledge')" icon="library" :href="route('knowledge.index')" variant="paper" size="regular" />
                <x-action-control :label="__('forum_expert_sessions.navigation.label')" icon="circle-help" :href="route('forum.expert-sessions.index')" variant="paper" size="regular" />
                @auth
                    <x-action-control :label="__('forum_journals.navigation.label')" icon="notebook-tabs" :href="route('forum.journals.index')" variant="paper" size="regular" />
                    <x-action-control :label="__('forum_groups.navigation.label')" icon="users" :href="route('groups.index')" variant="paper" size="regular" />
                    <x-action-control :label="__('forum_mentorship.navigation.label')" icon="users-round" :href="route('forum.mentorship.index')" variant="paper" size="regular" />
                @endauth
                <x-action-control :label="__('ui.ask_a_question')" icon="square-pen" :href="route('forum.topics.create')" variant="primary" size="regular" />
            </x-slot:actions>
        </x-page-header>

        <form method="GET" action="{{ route('forum.index') }}" class="forum-search" role="search">
            <label>
                <span class="sr-only">{{ __('ui.search_forum') }}</span>
                <x-ui-icon name="search" />
                <input name="q" value="{{ $filters['q'] }}" placeholder="{{ __('ui.search_questions_pets_places_or_exact_phrases') }}">
            </label>
            <select name="language" aria-label="{{ __('ui.topic_language') }}">
                <option value="all" @selected($filters['language'] === 'all')>{{ __('ui.all_languages') }}</option>
                <option value="en" @selected($filters['language'] === 'en')>{{ __('ui.english') }}</option>
                <option value="lt" @selected($filters['language'] === 'lt')>{{ __('ui.lithuanian') }}</option>
                <option value="ru" @selected($filters['language'] === 'ru')>{{ __('ui.russian') }}</option>
            </select>
            <button type="submit" class="forum-button forum-button--primary">
                <x-ui-icon name="search" />
                {{ __('ui.search') }}
            </button>
            <input type="hidden" name="category" value="{{ $filters['category'] }}">
            <input type="hidden" name="filter" value="{{ $filters['filter'] }}">
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        </form>

        <section class="forum-stats" aria-label="{{ __('ui.forum_summary') }}">
            @forelse ($stats as $stat)
                <div class="forum-stats__item">
                    <x-ui-icon :name="$stat['icon']" />
                    <div>
                        <strong>{{ $stat['value'] }}</strong>
                        <span>{{ $stat['label'] }}</span>
                    </div>
                </div>
            @empty
                <p>{{ __('ui.no_forum_statistics_yet') }}</p>
            @endforelse
        </section>

        <div class="forum-layout">
            <div class="forum-directory-main" data-forum-directory-main>
                <x-forum-category-navigator
                    :navigation="$category_navigation"
                    :filters="$filters"
                    :draft-count="$draft_count"
                />

                <div class="forum-directory-controls">
                    <nav class="forum-filter-tabs" aria-label="{{ __('ui.topic_filters') }}">
                        @forelse ($filter_options as $key => $label)
                            <a
                                href="{{ route('forum.index', [...$filters, 'filter' => $key, 'page' => null]) }}"
                                @if ($filters['filter'] === $key) aria-current="page" @endif
                            >{{ $label }}</a>
                        @empty
                            <span>{{ __('ui.no_filters_available') }}</span>
                        @endforelse
                    </nav>

                    <div class="forum-filter-tabs" aria-label="{{ __('ui.topic_sorting') }}">
                        @forelse ($sort_options as $key => $label)
                            <a
                                href="{{ route('forum.index', [...$filters, 'sort' => $key, 'page' => null]) }}"
                                @if ($filters['sort'] === $key) aria-current="page" @endif
                            >{{ $label }}</a>
                        @empty
                            <span>{{ __('ui.no_sort_options_available') }}</span>
                        @endforelse
                    </div>
                </div>

                <section class="forum-topic-list" aria-label="{{ __('ui.forum_topics') }}">
                    @forelse ($topics as $topic)
                        <x-forum-topic-card :topic="$topic" />
                    @empty
                        <div class="forum-form">
                            <h2>{{ __('ui.no_matching_discussion_yet') }}</h2>
                            <p>{{ __('ui.try_a_broader_phrase_or_start_a_focused_question_with_the_details_that_make_your_case_different') }}</p>
                            <a href="{{ route('forum.topics.create') }}" class="forum-button forum-button--primary">
                                <x-ui-icon name="square-pen" />
                                {{ __('ui.start_a_topic') }}
                            </a>
                        </div>
                    @endforelse
                </section>

                <div class="mt-5">{{ $topics->links() }}</div>
            </div>

            <aside class="forum-sidebar" aria-label="{{ __('ui.knowledge_and_notifications') }}">
                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title">
                        <span>{{ __('ui.knowledge_desk') }}</span>
                        <a href="{{ route('knowledge.index') }}" class="inline-flex items-center gap-1">
                            <span>{{ __('ui.all') }}</span>
                            <x-ui-icon name="arrow-right" size="xs" />
                        </a>
                    </div>
                    <div class="forum-mini-list">
                        @forelse ($knowledge as $article)
                            <a href="{{ route('knowledge.articles.show', $article['slug']) }}">
                                <x-ui-icon name="book-open-check" size="sm" />
                                <span>
                                    {{ $article['title'] }}
                                    <small>{{ __('presentation.publication_reviewed', ['type' => $article['type_label'], 'date' => $article['reviewed_label']]) }}</small>
                                </span>
                            </a>
                        @empty
                            <p>{{ __('ui.no_reviewed_guides_yet') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title">
                        <span>{{ __('ui.your_updates') }}</span>
                    </div>
                    <div class="forum-mini-list">
                        @forelse ($notifications as $notification)
                            <form method="POST" action="{{ route('forum.actions') }}">
                                @csrf
                                <input type="hidden" name="action" value="mark-notification-read">
                                <input type="hidden" name="notification_id" value="{{ $notification['id'] }}">
                                <button type="submit">
                                    <x-ui-icon name="bell-ring" size="sm" />
                                    <span>
                                        {{ $notification['title'] }}
                                        <small>{{ __('forum.notifications.summary', [
                                            'date' => $notification['created_label'],
                                            'state' => $notification['state_label'],
                                        ]) }}</small>
                                    </span>
                                </button>
                            </form>
                        @empty
                            <p>{{ __('ui.no_new_updates') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
