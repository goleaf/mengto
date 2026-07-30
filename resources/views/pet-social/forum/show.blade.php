<x-layout.app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="forum-page">
        <nav class="forum-filter-tabs" aria-label="Breadcrumb">
            <a href="{{ route('pet-social.forum.index') }}">
                <x-lucide-arrow-left aria-hidden="true" />
                Forum
            </a>
            <a href="{{ route('pet-social.knowledge.index') }}">Knowledge base</a>
        </nav>

        <div class="forum-thread-layout">
            <main class="forum-thread">
                @if ($topic['is_medical'])
                    <aside class="forum-safety" role="note">
                        <x-lucide-triangle-alert aria-hidden="true" />
                        <div>
                            <strong>The forum is not emergency veterinary care</strong>
                            <span>If breathing, consciousness, severe bleeding, poisoning, trauma, or urination is a concern, call a clinic now rather than waiting for replies.</span>
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
                                <figcaption class="forum-safety">
                                    <x-lucide-eye-off aria-hidden="true" />
                                    Sensitive media. Open only if you are comfortable viewing it.
                                </figcaption>
                            @endif
                            @if ($media['type'] === 'video')
                                <video controls preload="metadata" aria-label="{{ $media['alt'] }}">
                                    <source src="{{ $media['url'] }}">
                                </video>
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
                            <span class="sr-only">No tags.</span>
                        @endforelse
                    </div>

                    <div class="forum-topic-card__facts">
                        <span><x-lucide-message-square aria-hidden="true" /> {{ $topic['answers_count'] }} answers</span>
                        <span><x-lucide-messages-square aria-hidden="true" /> {{ $topic['comments_count'] }} comments</span>
                        <span><x-lucide-eye aria-hidden="true" /> {{ $topic['view_count'] }}</span>
                        @if ($topic['pet_name'])
                            <span><x-lucide-paw-print aria-hidden="true" /> {{ $topic['pet_name'] }} / {{ $topic['pet_species'] }} / {{ $topic['pet_age_label'] }}</span>
                        @endif
                    </div>

                    <div class="forum-actions">
                        <form method="POST" action="{{ route('pet-social.forum.actions') }}">
                            @csrf
                            <input type="hidden" name="action" value="toggle-bookmark">
                            <input type="hidden" name="topic_id" value="{{ $topic['id'] }}">
                            <button type="submit" class="forum-button" aria-pressed="{{ $engagement['is_bookmarked'] ? 'true' : 'false' }}">
                                <x-lucide-bookmark aria-hidden="true" />
                                {{ $engagement['is_bookmarked'] ? 'Saved' : 'Save' }}
                            </button>
                        </form>

                        @if ($can_manage)
                            <a href="{{ route('pet-social.forum.topics.edit', $topic['slug']) }}" class="forum-button">
                                <x-lucide-pencil aria-hidden="true" />
                                Edit
                            </a>
                            <form method="POST" action="{{ route('pet-social.forum.actions') }}">
                                @csrf
                                <input type="hidden" name="action" value="{{ $topic['status_value'] === 'resolved' ? 'reopen-topic' : 'resolve-topic' }}">
                                <input type="hidden" name="topic_id" value="{{ $topic['id'] }}">
                                <button type="submit" class="forum-button">
                                    <x-dynamic-component
                                        :component="$topic['status_value'] === 'resolved' ? 'lucide-rotate-ccw' : 'lucide-circle-check-big'"
                                        aria-hidden="true"
                                    />
                                    {{ $topic['status_value'] === 'resolved' ? 'Reopen' : 'Mark resolved' }}
                                </button>
                            </form>
                            @if ($topic['status_value'] === 'resolved' && $topic['has_accepted_answer'])
                                <form method="POST" action="{{ route('pet-social.forum.actions') }}">
                                    @csrf
                                    <input type="hidden" name="action" value="convert-to-knowledge">
                                    <input type="hidden" name="topic_id" value="{{ $topic['id'] }}">
                                    <button type="submit" class="forum-button">
                                        <x-lucide-library-big aria-hidden="true" />
                                        Create knowledge draft
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </article>

                <section aria-labelledby="answers-heading">
                    <div class="forum-header">
                        <div class="forum-header__copy">
                            <p class="forum-header__eyebrow">Community answers</p>
                            <h2 id="answers-heading">{{ count($answers) }} thoughtful {{ \Illuminate\Support\Str::plural('answer', count($answers)) }}</h2>
                        </div>
                    </div>

                    <div class="forum-topic-list">
                        @forelse ($answers as $answer)
                            <x-object.forum-answer :answer="$answer" :topic="$topic" :can-manage="$can_manage" />
                        @empty
                            <div class="forum-form">
                                <h3>This topic still needs an answer</h3>
                                <p>Share a relevant experience, a careful professional perspective, or a source tied to a specific claim.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                @if (! $topic['is_locked'])
                    <form method="POST" action="{{ route('pet-social.forum.answers.store', $topic['slug']) }}" class="forum-form">
                        @csrf
                        <div>
                            <p class="forum-header__eyebrow">Add an answer</p>
                            <h2>Contribute a clear next step</h2>
                        </div>
                        @if ($errors->any())
                            <div class="forum-errors" role="alert">{{ $errors->first() }}</div>
                        @endif
                        <label class="forum-form__field">
                            <span>Your answer</span>
                            <textarea name="body" minlength="20" maxlength="6000" required>{{ old('body') }}</textarea>
                            <small>Separate personal experience from professional claims and note important limits.</small>
                        </label>
                        <div class="forum-form__grid">
                            <label class="forum-form__field">
                                <span>Context</span>
                                <select name="experience_type" required>
                                    <option value="personal-experience">Personal experience</option>
                                    <option value="volunteer-experience">Volunteer experience</option>
                                    <option value="professional-opinion">Professional opinion</option>
                                    <option value="organization-experience">Organization experience</option>
                                    <option value="source-summary">Source summary</option>
                                </select>
                            </label>
                            <label class="forum-form__field">
                                <span>Sources, one URL per line</span>
                                <textarea name="sources" maxlength="1500">{{ old('sources') }}</textarea>
                            </label>
                        </div>
                        <button type="submit" class="forum-button forum-button--primary">
                            <x-lucide-send aria-hidden="true" />
                            Publish answer
                        </button>
                    </form>
                @endif
            </main>

            <aside class="forum-sidebar">
                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>Follow this topic</span></div>
                    <form method="POST" action="{{ route('pet-social.forum.actions') }}" class="forum-form">
                        @csrf
                        <input type="hidden" name="action" value="set-subscription">
                        <input type="hidden" name="topic_id" value="{{ $topic['id'] }}">
                        <label class="forum-form__field">
                            <span>Notifications</span>
                            <select name="value">
                                @forelse ($subscription_options as $key => $label)
                                    <option value="{{ $key }}" @selected($engagement['subscription_level'] === $key)>{{ $label }}</option>
                                @empty
                                    <option value="none">No notifications</option>
                                @endforelse
                            </select>
                        </label>
                        <button type="submit" class="forum-button">
                            <x-lucide-bell-ring aria-hidden="true" />
                            Update
                        </button>
                    </form>
                </section>

                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>Related questions</span></div>
                    <div class="forum-mini-list">
                        @forelse ($similar_topics as $similar)
                            <a href="{{ route('pet-social.forum.topics.show', $similar['slug']) }}">
                                {{ $similar['title'] }}
                                <small>{{ $similar['status_label'] }} / {{ $similar['answers_count'] }} answers</small>
                            </a>
                        @empty
                            <p>No close matches.</p>
                        @endforelse
                    </div>
                </section>

                <section class="forum-sidebar__section">
                    <div class="forum-sidebar__title"><span>Reviewed guides</span></div>
                    <div class="forum-mini-list">
                        @forelse ($related_articles as $article)
                            <a href="{{ route('pet-social.knowledge.articles.show', $article['slug']) }}">
                                {{ $article['title'] }}
                                <small>{{ $article['reviewed_label'] }}</small>
                            </a>
                        @empty
                            <p>No reviewed guide in this category yet.</p>
                        @endforelse
                    </div>
                </section>

                <section class="forum-sidebar__section">
                    <details>
                        <summary class="forum-button">
                            <x-lucide-shield-alert aria-hidden="true" />
                            Safety actions
                        </summary>
                        <div class="forum-topic-list mt-2">
                            <form method="POST" action="{{ route('pet-social.forum.actions') }}" class="forum-form">
                                @csrf
                                <input type="hidden" name="action" value="report-topic">
                                <input type="hidden" name="topic_id" value="{{ $topic['id'] }}">
                                <label class="forum-form__field">
                                    <span>Report reason</span>
                                    <select name="reason">
                                        <option value="dangerous-advice">Dangerous advice</option>
                                        <option value="personal-data">Personal data</option>
                                        <option value="animal-cruelty">Animal cruelty</option>
                                        <option value="fraud">Fraud</option>
                                        <option value="spam">Spam</option>
                                        <option value="other">Other</option>
                                    </select>
                                </label>
                                <button type="submit" class="forum-button forum-button--danger">
                                    <x-lucide-flag aria-hidden="true" />
                                    Report topic
                                </button>
                            </form>
                            @if (! $can_manage)
                                <form method="POST" action="{{ route('pet-social.forum.actions') }}">
                                    @csrf
                                    <input type="hidden" name="action" value="block-author">
                                    <input type="hidden" name="author_key" value="{{ $topic['author_key'] }}">
                                    <button type="submit" class="forum-button forum-button--danger">
                                        <x-lucide-user-x aria-hidden="true" />
                                        Block author
                                    </button>
                                </form>
                            @endif
                        </div>
                    </details>
                </section>
            </aside>
        </div>
    </div>
</x-layout.app-shell>
