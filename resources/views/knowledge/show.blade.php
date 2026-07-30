<x-layout.app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <nav class="forum-filter-tabs" aria-label="Breadcrumb">
            <a href="{{ route('knowledge.index') }}">
                <x-lucide-arrow-left aria-hidden="true" />
                Knowledge base
            </a>
            <a href="{{ route('forum.index', ['category' => $article['category']]) }}">Related forum topics</a>
        </nav>

        <div class="knowledge-layout">
            <main>
                <article class="knowledge-article">
                    <div class="forum-topic-card__meta">
                        <span class="forum-badge {{ $article['is_outdated'] ? 'forum-badge--danger' : '' }}">
                            <x-dynamic-component
                                :component="$article['is_outdated'] ? 'lucide-history' : 'lucide-book-open-check'"
                                aria-hidden="true"
                            />
                            {{ $article['status_label'] }}
                        </span>
                        <span>{{ $article['type_label'] }}</span>
                        <span>{{ $article['difficulty_label'] }}</span>
                        <span>Version {{ $article['version'] }}</span>
                    </div>

                    <h1>{{ $article['title'] }}</h1>
                    <p class="knowledge-article__summary">{{ $article['summary'] }}</p>

                    @if ($article['is_outdated'])
                        <aside class="forum-safety">
                            <x-lucide-history aria-hidden="true" />
                            <div>
                                <strong>This material may be outdated</strong>
                                <span>Check the listed primary sources and submit a correction if a rule, address, price, or service has changed.</span>
                            </div>
                        </aside>
                    @endif

                    <div class="forum-thread__body knowledge-article__body">{{ $article['body'] }}</div>

                    <div class="forum-topic-card__tags">
                        @forelse ($article['tags'] as $tag)
                            <span class="forum-topic-card__tag">{{ $tag }}</span>
                        @empty
                            <span class="sr-only">No tags.</span>
                        @endforelse
                    </div>
                </article>
            </main>

            <aside class="forum-sidebar">
                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>Review record</span></div>
                    <div class="forum-mini-list">
                        <span>Last reviewed <small>{{ $article['reviewed_label'] }}</small></span>
                        @if ($article['next_review_label'])
                            <span>Next review <small>{{ $article['next_review_label'] }}</small></span>
                        @endif
                        <span>Audience <small>{{ $article['audience'] }}</small></span>
                        <span>Language <small>{{ $article['language'] }}</small></span>
                    </div>
                </section>

                @if ($article['contributors'] !== [])
                    <section class="forum-sidebar__section">
                        <div class="forum-sidebar__title"><span>Contributors</span></div>
                        <div class="forum-mini-list">
                            @forelse ($article['contributors'] as $contributor)
                                <span>
                                    {{ $contributor['name'] }}
                                    <small>{{ $contributor['role'] }}</small>
                                </span>
                            @empty
                                <span>Editorial team</span>
                            @endforelse
                        </div>
                    </section>
                @endif

                @if ($article['sources'] !== [])
                    <section class="forum-sidebar__section">
                        <div class="forum-sidebar__title"><span>Sources</span></div>
                        <div class="forum-mini-list">
                            @forelse ($article['sources'] as $source)
                                <a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer">
                                    {{ $source['label'] }}
                                    <small>Open primary source</small>
                                </a>
                            @empty
                                <span>No sources listed.</span>
                            @endforelse
                        </div>
                    </section>
                @endif

                @if ($article['source_topic'])
                    <section class="forum-sidebar__section">
                        <div class="forum-sidebar__title"><span>Original discussion</span></div>
                        <div class="forum-mini-list">
                            <a href="{{ route('forum.topics.show', $article['source_topic']['slug']) }}">
                                {{ $article['source_topic']['title'] }}
                                <small>Read the community context</small>
                            </a>
                        </div>
                    </section>
                @endif

                <section class="forum-sidebar__section">
                    <details>
                        <summary class="forum-button">
                            <x-lucide-file-pen-line aria-hidden="true" />
                            Suggest correction
                        </summary>
                        <form method="POST" action="{{ route('knowledge.corrections.store', $article['slug']) }}" class="forum-form mt-2">
                            @csrf
                            <label class="forum-form__field">
                                <span>Section or field</span>
                                <select name="field" required>
                                    <option value="title">Title</option>
                                    <option value="summary">Summary</option>
                                    <option value="body">Main guidance</option>
                                    <option value="sources">Sources</option>
                                    <option value="local-details">Local details</option>
                                    <option value="review-date">Review date</option>
                                </select>
                            </label>
                            <label class="forum-form__field">
                                <span>What should change?</span>
                                <textarea name="suggestion" minlength="20" maxlength="2500" required></textarea>
                            </label>
                            <label class="forum-form__field">
                                <span>Current source</span>
                                <input type="url" name="source_url" maxlength="500">
                            </label>
                            <button type="submit" class="forum-button forum-button--primary">
                                <x-lucide-send aria-hidden="true" />
                                Send correction
                            </button>
                        </form>
                    </details>
                </section>

                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>Version history</span></div>
                    <div class="forum-mini-list">
                        @forelse ($versions as $version)
                            <span>
                                Version {{ $version->version_number }}
                                <small>{{ $version->change_summary }} / {{ $version->created_at->format('M j, Y') }}</small>
                            </span>
                        @empty
                            <span>No earlier revisions.</span>
                        @endforelse
                    </div>
                </section>

                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>Related guides</span></div>
                    <div class="forum-mini-list">
                        @forelse ($related as $relatedArticle)
                            <a href="{{ route('knowledge.articles.show', $relatedArticle['slug']) }}">
                                {{ $relatedArticle['title'] }}
                                <small>{{ $relatedArticle['reviewed_label'] }}</small>
                            </a>
                        @empty
                            <span>No related guides.</span>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-layout.app-shell>
