<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <nav class="forum-filter-tabs" aria-label="{{ __('ui.breadcrumb_2bd873d6c7') }}">
            <a href="{{ route('forum.index') }}">
                <x-lucide-arrow-left aria-hidden="true" />
                {{ __('ui.forum_4da7bd42ab') }}
            </a>
            <a href="{{ route('knowledge.index') }}">{{ __('ui.knowledge_base_f56819a30d') }}</a>
        </nav>

        <div class="forum-thread-layout">
            <div class="forum-thread">
                @if ($topic['is_medical'])
                    <aside class="forum-safety" role="note">
                        <x-lucide-triangle-alert aria-hidden="true" />
                        <div>
                            <strong>{{ __('ui.the_forum_is_not_emergency_veterinary_care_69525c5755') }}</strong>
                            <span>{{ __('ui.if_breathing_consciousness_severe_bleeding_poisoning_trauma_or_17efdebe9e') }}</span>
                        </div>
                    </aside>
                @endif

                <article class="forum-thread__header">
                    <div class="forum-topic-card__meta">
                        <span class="forum-badge {{ $topic['is_urgent'] ? 'forum-badge--danger' : '' }}">
                            {{ $topic['status_label'] }}
                        </span>
                        <span>{{ $topic['type_label'] }}</span>
                        <span>{{ $topic['category_label'] }}</span>
                        <span>{{ $topic['visibility_label'] }}</span>
                        @if ($topic['location'])
                            <span><x-lucide-map-pin aria-hidden="true" /> {{ $topic['location'] }}</span>
                        @endif
                    </div>

                    <h1 class="forum-thread__title">{{ $topic['title'] }}</h1>

                    <div class="forum-topic-card__author">
                        <span class="forum-topic-card__avatar" aria-hidden="true">{{ $topic['author_initials'] }}</span>
                        <span>
                            <strong>{{ $topic['author_name'] }}</strong>
                            <span>{{ $topic['author_role'] }} / {{ $topic['activity_label'] }}</span>
                        </span>
                    </div>

                    <div class="forum-thread__body">{{ $topic['body'] }}</div>

                    @forelse ($topic['media'] as $media)
                        <figure class="forum-thread__media">
                            @if ($media['sensitive'])
                                <div class="forum-safety">
                                    <x-lucide-eye-off aria-hidden="true" />
                                    {{ __('ui.sensitive_media_open_only_if_you_are_comfortable_6ce743c987') }}
                                </div>
                            @endif
                            @if ($media['type'] === 'video')
                                <video
                                    controls
                                    preload="metadata"
                                    aria-describedby="topic-media-description-{{ $loop->index }}"
                                >
                                    <source src="{{ $media['url'] }}">
                                    @if ($media['captions_url'])
                                        <track
                                            kind="captions"
                                            src="{{ $media['captions_url'] }}"
                                            srclang="{{ $media['caption_locale'] }}"
                                            label="{{ __('forum_accessibility.media.captions_label', ['locale' => $media['caption_locale']]) }}"
                                            default
                                        >
                                    @endif
                                </video>
                                <figcaption id="topic-media-description-{{ $loop->index }}" class="forum-thread__media-description">
                                    <strong>{{ $media['alt'] }}</strong>
                                    <details>
                                        <summary>{{ __('forum_accessibility.media.transcript_label') }}</summary>
                                        <p>{{ $media['transcript'] }}</p>
                                    </details>
                                </figcaption>
                            @else
                                <img src="{{ $media['url'] }}" alt="{{ $media['alt'] }}" width="1400" height="800">
                            @endif
                        </figure>
                    @empty
                    @endforelse

                    <div class="forum-topic-card__tags">
                        @forelse ($topic['tags'] as $tag)
                            <span class="forum-topic-card__tag">{{ $tag }}</span>
                        @empty
                            <span class="sr-only">{{ __('ui.no_tags_9098cf0d23') }}</span>
                        @endforelse
                    </div>

                    <div class="forum-topic-card__facts">
                        <span><x-lucide-message-square aria-hidden="true" /> {{ trans_choice('presentation.answers_count', $topic['answers_count'], ['count' => $topic['answers_count']]) }}</span>
                        <span><x-lucide-messages-square aria-hidden="true" /> {{ trans_choice('presentation.comments_count', $topic['comments_count'], ['count' => $topic['comments_count']]) }}</span>
                        <span><x-lucide-eye aria-hidden="true" /> {{ $topic['view_count'] }}</span>
                        @if ($topic['pet_name'])
                            <span><x-lucide-paw-print aria-hidden="true" /> {{ $topic['pet_name'] }} / {{ $topic['pet_species'] }} / {{ $topic['pet_age_label'] }}</span>
                        @endif
                    </div>

                    <div class="forum-actions">
                        <form method="POST" action="{{ route('forum.actions') }}">
                            @csrf
                            <input type="hidden" name="action" value="toggle-bookmark">
                            <input type="hidden" name="topic_id" value="{{ $topic['id'] }}">
                            <button type="submit" class="forum-button" aria-pressed="{{ $engagement['is_bookmarked'] ? 'true' : 'false' }}">
                                <x-lucide-bookmark aria-hidden="true" />
                                {{ $engagement['is_bookmarked'] ? __('ui.saved_b5c120b316') : __('ui.save_1509f561f2') }}
                            </button>
                        </form>

                        @if ($can_manage)
                            <a href="{{ route('forum.topics.edit', $topic['slug']) }}" class="forum-button">
                                <x-lucide-pencil aria-hidden="true" />
                                {{ __('ui.edit_464c4ffd01') }}
                            </a>
                            @if (in_array($topic['status_value'], ['solved', 'resolved'], true) && $topic['has_accepted_answer'])
                                <form method="POST" action="{{ route('forum.actions') }}">
                                    @csrf
                                    <input type="hidden" name="action" value="convert-to-knowledge">
                                    <input type="hidden" name="topic_id" value="{{ $topic['id'] }}">
                                    <button type="submit" class="forum-button">
                                        <x-lucide-library-big aria-hidden="true" />
                                        {{ __('ui.create_knowledge_draft_2e84d856b3') }}
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </article>

                <livewire:forum.forum-topic-lifecycle-panel :topic-id="$topic['id']" />

                @if ($journal_id !== null)
                    <livewire:forum.forum-journal-timeline :journal-id="$journal_id" />
                @endif

                @if ($journal_id === null || $answers !== [])
                    <section aria-labelledby="answers-heading">
                        <div class="forum-header">
                            <div class="forum-header__copy">
                                <p class="forum-header__eyebrow">{{ __('ui.community_answers_793d6c2f23') }}</p>
                                <h2 id="answers-heading">{{ trans_choice('presentation.thoughtful_answers', count($answers), ['count' => count($answers)]) }}</h2>
                            </div>
                        </div>

                        <div class="forum-topic-list">
                            @forelse ($answers as $answer)
                                <x-forum-answer :answer="$answer" :topic="$topic" :can-manage="$can_manage" />
                            @empty
                                <div class="forum-form">
                                    <h3>{{ __('ui.this_topic_still_needs_an_answer_0810c1f0fe') }}</h3>
                                    <p>{{ __('ui.share_a_relevant_experience_a_careful_professional_perspective_b7d2787789') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endif

                <livewire:forum.community-notes-panel :topic-id="$topic['id']" />

                @if ($can_answer && $journal_id === null)
                    <form method="POST" action="{{ route('forum.answers.store', $topic['slug']) }}" class="forum-form">
                        @csrf
                        <div>
                            <p class="forum-header__eyebrow">{{ __('ui.add_an_answer_63cebd92d5') }}</p>
                            <h2>{{ __('ui.contribute_a_clear_next_step_3a72805f08') }}</h2>
                        </div>
                        @if ($errors->any())
                            <x-forum-error-summary :messages="$errors->getMessages()" />
                        @endif
                        <label class="forum-form__field">
                            <span>{{ __('ui.your_answer_d0e869b777') }}</span>
                            <textarea name="body" minlength="20" maxlength="6000" required>{{ old('body') }}</textarea>
                            <small>{{ __('ui.separate_personal_experience_from_professional_claims_and_note_3d87bc6905') }}</small>
                        </label>
                        <div class="forum-form__grid">
                            <label class="forum-form__field">
                                <span>{{ __('ui.context_a6e600a10f') }}</span>
                                <select name="experience_type" required>
                                    <option value="personal-experience">{{ __('ui.personal_experience_b46280093b') }}</option>
                                    <option value="volunteer-experience">{{ __('ui.volunteer_experience_7a7c500571') }}</option>
                                    <option value="professional-opinion">{{ __('ui.professional_opinion_26d50b58ad') }}</option>
                                    <option value="organization-experience">{{ __('ui.organization_experience_3d7079e87b') }}</option>
                                    <option value="source-summary">{{ __('ui.source_summary_7ec78b6389') }}</option>
                                </select>
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('ui.sources_one_url_per_line_b360f9a707') }}</span>
                                <textarea name="sources" maxlength="1500">{{ old('sources') }}</textarea>
                            </label>
                        </div>
                        <button type="submit" class="forum-button forum-button--primary">
                            <x-lucide-send aria-hidden="true" />
                            {{ __('ui.publish_answer_a87ef6402e') }}
                        </button>
                    </form>
                @endif
            </div>

            <aside class="forum-sidebar">
                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>{{ __('ui.follow_this_topic_5b12931d8c') }}</span></div>
                    <form method="POST" action="{{ route('forum.actions') }}" class="forum-form">
                        @csrf
                        <input type="hidden" name="action" value="set-subscription">
                        <input type="hidden" name="topic_id" value="{{ $topic['id'] }}">
                        <label class="forum-form__field">
                            <span>{{ __('ui.notifications_788011833a') }}</span>
                            <select name="value">
                                @forelse ($subscription_options as $key => $label)
                                    <option value="{{ $key }}" @selected($engagement['subscription_level'] === $key)>{{ $label }}</option>
                                @empty
                                    <option value="none">{{ __('ui.no_notifications_cbce2040cc') }}</option>
                                @endforelse
                            </select>
                        </label>
                        <button type="submit" class="forum-button">
                            <x-lucide-bell-ring aria-hidden="true" />
                            {{ __('ui.update_c1c1009d3f') }}
                        </button>
                    </form>
                </section>

                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>{{ __('ui.related_questions_3b91cba168') }}</span></div>
                    <div class="forum-mini-list">
                        @forelse ($similar_topics as $similar)
                            <a href="{{ route('forum.topics.show', $similar['slug']) }}">
                                {{ $similar['title'] }}
                                <small>{{ __('presentation.status_answers', ['status' => $similar['status_label'], 'answers' => $similar['answers_count']]) }}</small>
                            </a>
                        @empty
                            <p>{{ __('ui.no_close_matches_319d415e94') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>{{ __('ui.reviewed_guides_8aacaa0f77') }}</span></div>
                    <div class="forum-mini-list">
                        @forelse ($related_articles as $article)
                            <a href="{{ route('knowledge.articles.show', $article['slug']) }}">
                                {{ $article['title'] }}
                                <small>{{ $article['reviewed_label'] }}</small>
                            </a>
                        @empty
                            <p>{{ __('ui.no_reviewed_guide_in_this_category_yet_d43d89639b') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="forum-sidebar__section">
                    <details>
                        <summary class="forum-button">
                            <x-lucide-shield-alert aria-hidden="true" />
                            {{ __('ui.safety_actions_c582dd7800') }}
                        </summary>
                        <div class="forum-topic-list mt-2">
                            <form method="POST" action="{{ route('forum.actions') }}" class="forum-form">
                                @csrf
                                <input type="hidden" name="action" value="report-topic">
                                <input type="hidden" name="topic_id" value="{{ $topic['id'] }}">
                                <label class="forum-form__field">
                                    <span>{{ __('ui.report_reason_db3a509076') }}</span>
                                    <select name="reason">
                                        <option value="dangerous-advice">{{ __('ui.dangerous_advice_df40777716') }}</option>
                                        <option value="personal-data">{{ __('ui.personal_data_434886e6b6') }}</option>
                                        <option value="animal-cruelty">{{ __('ui.animal_cruelty_6020404186') }}</option>
                                        <option value="fraud">{{ __('ui.fraud_1baabd4791') }}</option>
                                        <option value="spam">{{ __('ui.spam_94a9eac404') }}</option>
                                        <option value="other">{{ __('ui.other_f97e9da0e3') }}</option>
                                    </select>
                                </label>
                                <label class="forum-form__check">
                                    <input type="checkbox" name="truthfulness_confirmed" value="1" required>
                                    <span>{{ __('forum_moderation.forms.truthfulness') }}</span>
                                </label>
                                <label class="forum-form__check">
                                    <input type="checkbox" name="immediate_safety" value="1">
                                    <span>{{ __('forum_moderation.forms.immediate_safety') }}</span>
                                </label>
                                <label class="forum-form__check">
                                    <input type="checkbox" name="block_user" value="1">
                                    <span>{{ __('forum_moderation.forms.block_user') }}</span>
                                </label>
                                <button type="submit" class="forum-button forum-button--danger">
                                    <x-lucide-flag aria-hidden="true" />
                                    {{ __('ui.report_topic_918ef4030a') }}
                                </button>
                            </form>
                            @if (! $can_manage)
                                <form method="POST" action="{{ route('forum.actions') }}">
                                    @csrf
                                    <input type="hidden" name="action" value="block-author">
                                    <input type="hidden" name="author_key" value="{{ $topic['author_key'] }}">
                                    <button type="submit" class="forum-button forum-button--danger">
                                        <x-lucide-user-x aria-hidden="true" />
                                        {{ __('ui.block_author_0252bd42c3') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </details>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
