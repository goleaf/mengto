<x-layout.app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <header class="forum-header">
            <div class="forum-header__copy">
                <p class="forum-header__eyebrow">Community knowledge</p>
                <h1>Ask well. Find what lasts.</h1>
                <p>Questions, field notes, expert context, and practical guides that remain useful after the feed moves on.</p>
            </div>
            <div class="forum-header__actions">
                <a href="{{ route('pet-social.knowledge.index') }}" class="forum-button">
                    <x-lucide-library aria-hidden="true" />
                    Knowledge
                </a>
                <a href="{{ route('pet-social.forum.topics.create') }}" class="forum-button forum-button--primary">
                    <x-lucide-square-pen aria-hidden="true" />
                    Ask a question
                </a>
            </div>
        </header>

        <form method="GET" action="{{ route('pet-social.forum.index') }}" class="forum-search" role="search">
            <label>
                <span class="sr-only">Search forum</span>
                <x-lucide-search aria-hidden="true" />
                <input name="q" value="{{ $filters['q'] }}" placeholder="Search questions, pets, places, or exact phrases">
            </label>
            <select name="language" aria-label="Topic language">
                <option value="all" @selected($filters['language'] === 'all')>All languages</option>
                <option value="en" @selected($filters['language'] === 'en')>English</option>
                <option value="lt" @selected($filters['language'] === 'lt')>Lithuanian</option>
                <option value="ru" @selected($filters['language'] === 'ru')>Russian</option>
            </select>
            <button type="submit" class="forum-button forum-button--primary">
                <x-lucide-search aria-hidden="true" />
                Search
            </button>
            <input type="hidden" name="category" value="{{ $filters['category'] }}">
            <input type="hidden" name="filter" value="{{ $filters['filter'] }}">
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        </form>

        <section class="forum-stats" aria-label="Forum summary">
            @forelse ($stats as $stat)
                <div class="forum-stats__item">
                    <x-dynamic-component :component="'lucide-'.$stat['icon']" aria-hidden="true" />
                    <div>
                        <strong>{{ $stat['value'] }}</strong>
                        <span>{{ $stat['label'] }}</span>
                    </div>
                </div>
            @empty
                <p>No forum statistics yet.</p>
            @endforelse
        </section>

        <div class="forum-layout">
            <aside class="forum-sidebar" aria-label="Forum categories">
                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title">
                        <span>Categories</span>
                        @if ($draft_count > 0)
                            <span class="forum-badge forum-badge--sun">{{ $draft_count }} draft</span>
                        @endif
                    </div>
                    <nav class="forum-categories">
                        <a
                            href="{{ route('pet-social.forum.index', [...$filters, 'category' => 'all', 'page' => null]) }}"
                            @if ($filters['category'] === 'all') aria-current="page" @endif
                        >
                            <x-lucide-layout-grid aria-hidden="true" />
                            All topics
                        </a>
                        @forelse ($categories as $key => $category)
                            <a
                                href="{{ route('pet-social.forum.index', [...$filters, 'category' => $key, 'page' => null]) }}"
                                @if ($filters['category'] === $key) aria-current="page" @endif
                            >
                                <x-dynamic-component :component="'lucide-'.$category['icon']" aria-hidden="true" />
                                {{ $category['label'] }}
                            </a>
                        @empty
                            <span>No categories available.</span>
                        @endforelse
                    </nav>
                </section>
            </aside>

            <main>
                <nav class="forum-filter-tabs" aria-label="Topic filters">
                    @forelse ($filter_options as $key => $label)
                        <a
                            href="{{ route('pet-social.forum.index', [...$filters, 'filter' => $key, 'page' => null]) }}"
                            @if ($filters['filter'] === $key) aria-current="page" @endif
                        >{{ $label }}</a>
                    @empty
                        <span>No filters available.</span>
                    @endforelse
                </nav>

                <div class="forum-filter-tabs" aria-label="Topic sorting">
                    @forelse ($sort_options as $key => $label)
                        <a
                            href="{{ route('pet-social.forum.index', [...$filters, 'sort' => $key, 'page' => null]) }}"
                            @if ($filters['sort'] === $key) aria-current="page" @endif
                        >{{ $label }}</a>
                    @empty
                        <span>No sort options available.</span>
                    @endforelse
                </div>

                <section class="forum-topic-list" aria-label="Forum topics">
                    @forelse ($topics as $topic)
                        <x-object.forum-topic-card :topic="$topic" />
                    @empty
                        <div class="forum-form">
                            <h2>No matching discussion yet</h2>
                            <p>Try a broader phrase or start a focused question with the details that make your case different.</p>
                            <a href="{{ route('pet-social.forum.topics.create') }}" class="forum-button forum-button--primary">
                                <x-lucide-square-pen aria-hidden="true" />
                                Start a topic
                            </a>
                        </div>
                    @endforelse
                </section>

                <div class="mt-5">{{ $topics->links() }}</div>
            </main>

            <aside class="forum-sidebar" aria-label="Knowledge and notifications">
                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title">
                        <span>Knowledge desk</span>
                        <a href="{{ route('pet-social.knowledge.index') }}">All</a>
                    </div>
                    <div class="forum-mini-list">
                        @forelse ($knowledge as $article)
                            <a href="{{ route('pet-social.knowledge.articles.show', $article['slug']) }}">
                                {{ $article['title'] }}
                                <small>{{ $article['type_label'] }} / reviewed {{ $article['reviewed_label'] }}</small>
                            </a>
                        @empty
                            <p>No reviewed guides yet.</p>
                        @endforelse
                    </div>
                </section>

                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title">
                        <span>Your updates</span>
                    </div>
                    <div class="forum-mini-list">
                        @forelse ($notifications as $notification)
                            <form method="POST" action="{{ route('pet-social.forum.actions') }}">
                                @csrf
                                <input type="hidden" name="action" value="mark-notification-read">
                                <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                <button type="submit">
                                    {{ $notification->title }}
                                    <small>{{ $notification->created_at->diffForHumans() }}{{ $notification->read_at ? ' / read' : ' / new' }}</small>
                                </button>
                            </form>
                        @empty
                            <p>No new updates.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-layout.app-shell>
