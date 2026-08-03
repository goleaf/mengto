<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <nav class="forum-filter-tabs" aria-label="{{ __('ui.breadcrumb_2bd873d6c7') }}">
            <a href="{{ route('knowledge.index') }}">
                <x-ui-icon name="arrow-left" />
                {{ __('ui.knowledge_base_f56819a30d') }}
            </a>
            <a href="{{ route('forum.index', ['category' => $article['category']]) }}">
                <x-ui-icon name="message-circle-question" />
                {{ __('ui.related_forum_topics_7266208c18') }}
            </a>
            <a href="{{ route('knowledge.articles.print', $article['slug']) }}">
                <x-ui-icon name="printer" />
                {{ __('knowledge.actions.print') }}
            </a>
            <a href="{{ route('knowledge.articles.export', $article['slug']) }}">
                <x-ui-icon name="download" />
                {{ __('knowledge.actions.export') }}
            </a>
            @if ($article['can_edit'])
                <a href="{{ route('knowledge.guides.edit', $article['slug']) }}">
                    <x-ui-icon name="file-pen-line" />
                    {{ __('knowledge.actions.edit') }}
                </a>
            @endif
            @if ($article['can_translate'])
                <a href="{{ route('knowledge.guides.translations.create', $article['slug']) }}">
                    <x-ui-icon name="languages" />
                    {{ __('knowledge.actions.translate') }}
                </a>
            @endif
        </nav>

        <div class="knowledge-layout">
            <div>
                <article class="knowledge-article">
                    <div class="forum-topic-card__meta">
                        <span class="forum-badge {{ $article['is_outdated'] ? 'forum-badge--danger' : '' }}">
                            <x-ui-icon :name="$article['is_outdated'] ? 'history' : 'book-open-check'" />
                            {{ $article['status_label'] }}
                        </span>
                        <span>{{ $article['type_label'] }}</span>
                        <span>{{ $article['difficulty_label'] }}</span>
                        <span>{{ __('presentation.version', ['version' => $article['version']]) }}</span>
                    </div>

                    <h1>{{ $article['title'] }}</h1>
                    <p class="knowledge-article__summary">{{ $article['summary'] }}</p>

                    @if ($article['is_outdated'])
                        <aside class="forum-safety">
                            <x-ui-icon name="history" />
                            <div>
                                <strong>{{ __('ui.this_material_may_be_outdated_87a18e480c') }}</strong>
                                <span>{{ __('ui.check_the_listed_primary_sources_and_submit_a_7065642b8d') }}</span>
                            </div>
                        </aside>
                    @endif

                    @if ($article['translation'] !== null)
                        <aside class="forum-safety" aria-labelledby="knowledge-translation-attribution-heading">
                            <x-ui-icon name="languages" />
                            <div>
                                <strong id="knowledge-translation-attribution-heading">
                                    {{ $article['translation']['source_label'] }}
                                </strong>
                                @if ($article['translation']['source_article'] !== null)
                                    <span>
                                        {{ __('knowledge.translations.translated_from') }}
                                        <a href="{{ route('knowledge.articles.show', $article['translation']['source_article']['slug']) }}">
                                            {{ $article['translation']['source_article']['title'] }}
                                        </a>
                                        · {{ $article['translation']['source_article']['language'] }}
                                    </span>
                                @endif
                                @if ($article['translation']['translator'] !== null)
                                    <span>
                                        {{ __('knowledge.translations.translated_by', [
                                            'name' => $article['translation']['translator'],
                                        ]) }}
                                    </span>
                                @endif
                                <span>{{ __('knowledge.translations.correction_available') }}</span>
                            </div>
                        </aside>
                    @endif

                    <div class="forum-thread__body knowledge-article__body">{{ $article['body'] }}</div>

                    <div class="forum-topic-card__tags">
                        @forelse ($article['tags'] as $tag)
                            <span class="forum-topic-card__tag">{{ $tag }}</span>
                        @empty
                            <span class="sr-only">{{ __('ui.no_tags_9098cf0d23') }}</span>
                        @endforelse
                    </div>
                </article>
            </div>

            <aside class="forum-sidebar">
                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>{{ __('ui.review_record_8f17cdae59') }}</span></div>
                    <div class="forum-mini-list">
                        <span>{{ __('ui.last_reviewed_a3b600801a') }} <small>{{ $article['reviewed_label'] }}</small></span>
                        @if ($article['next_review_label'])
                            <span>{{ __('ui.next_review_31151e9f9b') }} <small>{{ $article['next_review_label'] }}</small></span>
                        @endif
                        <span>{{ __('ui.audience_545c023576') }} <small>{{ $article['audience'] }}</small></span>
                        <span>{{ __('ui.language_a4fe65264e') }} <small>{{ $article['language'] }}</small></span>
                        @if ($article['jurisdiction'])
                            <span>{{ __('knowledge.fields.jurisdiction') }} <small>{{ $article['jurisdiction'] }}</small></span>
                        @endif
                        @if ($article['taxon'])
                            <span>
                                {{ __('knowledge.fields.taxon') }}
                                <small>{{ $article['taxon']['scientific_name'] }} · {{ $article['taxon']['rank'] }}</small>
                            </span>
                        @endif
                    </div>
                </section>

                @if ($article['contributors'] !== [])
                    <section class="forum-sidebar__section">
                        <div class="forum-sidebar__title"><span>{{ __('ui.contributors_350ad433c4') }}</span></div>
                        <div class="forum-mini-list">
                            @forelse ($article['contributors'] as $contributor)
                                <span>
                                    {{ $contributor['name'] }}
                                    <small>{{ $contributor['role'] }}</small>
                                </span>
                            @empty
                                <span>{{ __('ui.editorial_team_d27e8a1e81') }}</span>
                            @endforelse
                        </div>
                    </section>
                @endif

                @if ($article['sources'] !== [])
                    <section class="forum-sidebar__section">
                        <div class="forum-sidebar__title"><span>{{ __('ui.sources_caf85b0888') }}</span></div>
                        <div class="forum-mini-list">
                            @forelse ($article['sources'] as $source)
                                <a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer">
                                    <x-ui-icon name="external-link" size="sm" />
                                    <span>
                                        {{ $source['label'] }}
                                        <small>{{ __('ui.open_primary_source_954956190f') }}</small>
                                    </span>
                                </a>
                            @empty
                                <span>{{ __('ui.no_sources_listed_5a688e193a') }}</span>
                            @endforelse
                        </div>
                    </section>
                @endif

                @if ($article['source_topic'])
                    <section class="forum-sidebar__section">
                        <div class="forum-sidebar__title"><span>{{ __('ui.original_discussion_2ead0bc4f7') }}</span></div>
                        <div class="forum-mini-list">
                            <a href="{{ route('forum.topics.show', $article['source_topic']['slug']) }}">
                                <x-ui-icon name="message-square" size="sm" />
                                <span>
                                    {{ $article['source_topic']['title'] }}
                                    <small>{{ __('ui.read_the_community_context_328f006e2d') }}</small>
                                </span>
                            </a>
                        </div>
                    </section>
                @endif

                @if ($article['discussion_topic'])
                    <section class="forum-sidebar__section">
                        <div class="forum-sidebar__title"><span>{{ __('knowledge.discussion.heading') }}</span></div>
                        <div class="forum-mini-list">
                            <a href="{{ route('forum.topics.show', $article['discussion_topic']['slug']) }}">
                                <x-ui-icon name="message-circle-question" size="sm" />
                                <span>
                                    {{ $article['discussion_topic']['title'] }}
                                    <small>{{ __('knowledge.discussion.open') }}</small>
                                </span>
                            </a>
                        </div>
                    </section>
                @endif

                @if ($article['replacement'])
                    <section class="forum-sidebar__section">
                        <div class="forum-sidebar__title"><span>{{ __('knowledge.replacement.heading') }}</span></div>
                        <div class="forum-mini-list">
                            <a href="{{ route('knowledge.articles.show', $article['replacement']['slug']) }}">
                                <x-ui-icon name="history" size="sm" />
                                <span>
                                    {{ $article['replacement']['title'] }}
                                    <small>{{ __('knowledge.replacement.open') }}</small>
                                </span>
                            </a>
                        </div>
                    </section>
                @endif

                @if ($article['translations'] !== [])
                    <section class="forum-sidebar__section">
                        <div class="forum-sidebar__title"><span>{{ __('knowledge.translations.heading') }}</span></div>
                        <div class="forum-mini-list">
                            @foreach ($article['translations'] as $translation)
                                <a href="{{ route('knowledge.articles.show', $translation['slug']) }}">
                                    <x-ui-icon name="languages" size="sm" />
                                    <span>
                                        {{ $translation['title'] }}
                                        <small>{{ $translation['language'] }}</small>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="forum-sidebar__section">
                    <details>
                        <summary class="forum-button">
                            <x-ui-icon name="file-pen-line" />
                            {{ __('ui.suggest_correction_c956d0ef71') }}
                        </summary>
                        <form method="POST" action="{{ route('knowledge.corrections.store', $article['slug']) }}" class="forum-form mt-2">
                            @csrf
                            <label class="forum-form__field">
                                <span>{{ __('ui.section_or_field_4c6a90c6e3') }}</span>
                                <select name="field" required>
                                    <option value="title">{{ __('ui.title_7e8cd2056d') }}</option>
                                    <option value="summary">{{ __('ui.summary_8e76a94ac8') }}</option>
                                    <option value="body">{{ __('ui.main_guidance_3b90d1a13f') }}</option>
                                    <option value="sources">{{ __('ui.sources_caf85b0888') }}</option>
                                    <option value="local-details">{{ __('ui.local_details_a144970836') }}</option>
                                    <option value="review-date">{{ __('ui.review_date_1c205cbb90') }}</option>
                                </select>
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('ui.what_should_change_e6cbf7faa1') }}</span>
                                <textarea name="suggestion" minlength="20" maxlength="2500" required></textarea>
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('ui.current_source_fc8a8bfccc') }}</span>
                                <input type="url" name="source_url" maxlength="500">
                            </label>
                            <button type="submit" class="forum-button forum-button--primary">
                                <x-ui-icon name="send" />
                                {{ __('ui.send_correction_2ce2343147') }}
                            </button>
                        </form>
                    </details>
                </section>

                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>{{ __('ui.version_history_a6df11e706') }}</span></div>
                    <div class="forum-mini-list">
                        @forelse ($versions as $version)
                            <span>
                                {{ __('presentation.version', ['version' => $version['number']]) }}
                                <small>{{ $version['summary'] }} / {{ $version['created'] }}</small>
                            </span>
                        @empty
                            <span>{{ __('ui.no_earlier_revisions_824ac356f1') }}</span>
                        @endforelse
                    </div>
                </section>

                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>{{ __('ui.related_guides_1e5b36078e') }}</span></div>
                    <div class="forum-mini-list">
                        @forelse ($related as $relatedArticle)
                            <a href="{{ route('knowledge.articles.show', $relatedArticle['slug']) }}">
                                <x-ui-icon name="book-open-check" size="sm" />
                                <span>
                                    {{ $relatedArticle['title'] }}
                                    <small>{{ $relatedArticle['reviewed_label'] }}</small>
                                </span>
                            </a>
                        @empty
                            <span>{{ __('ui.no_related_guides_d9e81b9fe7') }}</span>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
