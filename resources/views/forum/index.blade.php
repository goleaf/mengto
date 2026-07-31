<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <header class="forum-header">
            <div class="forum-header__copy">
                <p class="forum-header__eyebrow">{{ __('ui.community_knowledge_31eb615b90') }}</p>
                <h1>{{ __('ui.ask_well_find_what_lasts_3c2fdf9b45') }}</h1>
                <p>{{ __('ui.questions_field_notes_expert_context_and_practical_guides_5f0c917aa8') }}</p>
            </div>
            <div class="forum-header__actions">
                <a href="{{ route('knowledge.index') }}" class="forum-button">
                    <x-lucide-library aria-hidden="true" />
                    {{ __('ui.knowledge_dcb3e1c00e') }}
                </a>
                @auth
                    <a href="{{ route('forum.groups.index') }}" class="forum-button">
                        <x-lucide-users aria-hidden="true" />
                        {{ __('forum_groups.navigation.label') }}
                    </a>
                    <a href="{{ route('forum.mentorship.index') }}" class="forum-button">
                        <x-lucide-users-round aria-hidden="true" />
                        {{ __('forum_mentorship.navigation.label') }}
                    </a>
                @endauth
                <a href="{{ route('forum.topics.create') }}" class="forum-button forum-button--primary">
                    <x-lucide-square-pen aria-hidden="true" />
                    {{ __('ui.ask_a_question_3a533d7ef8') }}
                </a>
            </div>
        </header>

        <form method="GET" action="{{ route('forum.index') }}" class="forum-search" role="search">
            <label>
                <span class="sr-only">{{ __('ui.search_forum_aae59cf0ad') }}</span>
                <x-lucide-search aria-hidden="true" />
                <input name="q" value="{{ $filters['q'] }}" placeholder="{{ __('ui.search_questions_pets_places_or_exact_phrases_5190090592') }}">
            </label>
            <select name="language" aria-label="{{ __('ui.topic_language_8d3b8b5b39') }}">
                <option value="all" @selected($filters['language'] === 'all')>{{ __('ui.all_languages_acce3d0e30') }}</option>
                <option value="en" @selected($filters['language'] === 'en')>{{ __('ui.english_ba118bf7fc') }}</option>
                <option value="lt" @selected($filters['language'] === 'lt')>{{ __('ui.lithuanian_8625f6a206') }}</option>
                <option value="ru" @selected($filters['language'] === 'ru')>{{ __('ui.russian_5bcc40adf6') }}</option>
            </select>
            <button type="submit" class="forum-button forum-button--primary">
                <x-lucide-search aria-hidden="true" />
                {{ __('ui.search_49c266baaa') }}
            </button>
            <input type="hidden" name="category" value="{{ $filters['category'] }}">
            <input type="hidden" name="filter" value="{{ $filters['filter'] }}">
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        </form>

        <section class="forum-stats" aria-label="{{ __('ui.forum_summary_b01b2e0a58') }}">
            @forelse ($stats as $stat)
                <div class="forum-stats__item">
                    <x-dynamic-component :component="'lucide-'.$stat['icon']" aria-hidden="true" />
                    <div>
                        <strong>{{ $stat['value'] }}</strong>
                        <span>{{ $stat['label'] }}</span>
                    </div>
                </div>
            @empty
                <p>{{ __('ui.no_forum_statistics_yet_0b02e01738') }}</p>
            @endforelse
        </section>

        <div class="forum-layout">
            <aside class="forum-sidebar" aria-label="{{ __('ui.forum_categories_053db2cf7c') }}">
                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title">
                        <span>{{ __('ui.categories_b8b1d894c6') }}</span>
                        @if ($draft_count > 0)
                            <span class="forum-badge forum-badge--sun">{{ trans_choice('presentation.draft_count', $draft_count, ['count' => $draft_count]) }}</span>
                        @endif
                    </div>
                    <nav class="forum-categories">
                        <a
                            href="{{ route('forum.index', [...$filters, 'category' => 'all', 'page' => null]) }}"
                            @if ($filters['category'] === 'all') aria-current="page" @endif
                        >
                            <x-lucide-layout-grid aria-hidden="true" />
                            {{ __('ui.all_topics_29366ff597') }}
                        </a>
                        @forelse ($categories as $key => $category)
                            <a
                                href="{{ route('forum.index', [...$filters, 'category' => $key, 'page' => null]) }}"
                                @if ($filters['category'] === $key) aria-current="page" @endif
                            >
                                <x-dynamic-component :component="'lucide-'.$category['icon']" aria-hidden="true" />
                                {{ $category['label'] }}
                            </a>
                        @empty
                            <span>{{ __('ui.no_categories_available_a557500f61') }}</span>
                        @endforelse
                    </nav>
                </section>
            </aside>

            <main>
                <nav class="forum-filter-tabs" aria-label="{{ __('ui.topic_filters_9250e8d56b') }}">
                    @forelse ($filter_options as $key => $label)
                        <a
                            href="{{ route('forum.index', [...$filters, 'filter' => $key, 'page' => null]) }}"
                            @if ($filters['filter'] === $key) aria-current="page" @endif
                        >{{ $label }}</a>
                    @empty
                        <span>{{ __('ui.no_filters_available_dc23b63725') }}</span>
                    @endforelse
                </nav>

                <div class="forum-filter-tabs" aria-label="{{ __('ui.topic_sorting_199040afbe') }}">
                    @forelse ($sort_options as $key => $label)
                        <a
                            href="{{ route('forum.index', [...$filters, 'sort' => $key, 'page' => null]) }}"
                            @if ($filters['sort'] === $key) aria-current="page" @endif
                        >{{ $label }}</a>
                    @empty
                        <span>{{ __('ui.no_sort_options_available_0b2341d59b') }}</span>
                    @endforelse
                </div>

                <section class="forum-topic-list" aria-label="{{ __('ui.forum_topics_dfec5e5f89') }}">
                    @forelse ($topics as $topic)
                        <x-forum-topic-card :topic="$topic" />
                    @empty
                        <div class="forum-form">
                            <h2>{{ __('ui.no_matching_discussion_yet_1dee81e8c3') }}</h2>
                            <p>{{ __('ui.try_a_broader_phrase_or_start_a_focused_a511e7e0d2') }}</p>
                            <a href="{{ route('forum.topics.create') }}" class="forum-button forum-button--primary">
                                <x-lucide-square-pen aria-hidden="true" />
                                {{ __('ui.start_a_topic_c27175f4e0') }}
                            </a>
                        </div>
                    @endforelse
                </section>

                <div class="mt-5">{{ $topics->links() }}</div>
            </main>

            <aside class="forum-sidebar" aria-label="{{ __('ui.knowledge_and_notifications_b349fb1522') }}">
                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title">
                        <span>{{ __('ui.knowledge_desk_518296addd') }}</span>
                        <a href="{{ route('knowledge.index') }}">{{ __('ui.all_a52ace420f') }}</a>
                    </div>
                    <div class="forum-mini-list">
                        @forelse ($knowledge as $article)
                            <a href="{{ route('knowledge.articles.show', $article['slug']) }}">
                                {{ $article['title'] }}
                                <small>{{ __('presentation.publication_reviewed', ['type' => $article['type_label'], 'date' => $article['reviewed_label']]) }}</small>
                            </a>
                        @empty
                            <p>{{ __('ui.no_reviewed_guides_yet_3d9f862601') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title">
                        <span>{{ __('ui.your_updates_438558961b') }}</span>
                    </div>
                    <div class="forum-mini-list">
                        @forelse ($notifications as $notification)
                            <form method="POST" action="{{ route('forum.actions') }}">
                                @csrf
                                <input type="hidden" name="action" value="mark-notification-read">
                                <input type="hidden" name="notification_id" value="{{ $notification['id'] }}">
                                <button type="submit">
                                    {{ $notification->title }}
                                    <small>{{ __('forum.notifications.summary', [
                                        'date' => $notification['created_label'],
                                        'state' => $notification['state_label'],
                                    ]) }}</small>
                                </button>
                            </form>
                        @empty
                            <p>{{ __('ui.no_new_updates_5a45a6c539') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
