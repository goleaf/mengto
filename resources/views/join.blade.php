<x-join-layout
    :page-title="$page_title"
    :meta-description="$meta_description"
    :canonical-url="$canonical_url"
    :html-locale="$html_locale"
    :register-url="$register_url"
    :login-url="$login_url"
>
    <main id="main-content" data-join-page tabindex="-1">
        <section class="join-section join-hero" data-join-section aria-labelledby="join-hero-title">
            <div class="join-container join-hero__grid">
                <div class="join-hero__copy">
                    <p class="join-eyebrow">{{ __('join.hero.eyebrow') }}</p>
                    <h1 id="join-hero-title">{{ __('join.hero.title') }}</h1>
                    <p class="join-hero__description">{{ __('join.hero.description') }}</p>

                    <div class="join-hero__actions">
                        <a href="{{ $register_url }}" class="join-button join-button--primary" data-join-primary>
                            <span>{{ __('join.hero.primary_action') }}</span>
                            <x-ui-icon name="arrow-up-right" />
                        </a>
                        <a href="#join-possibilities" class="join-button join-button--secondary">
                            <x-ui-icon name="layout-grid" />
                            <span>{{ __('join.hero.secondary_action') }}</span>
                        </a>
                    </div>

                    <p class="join-hero__note">
                        <x-ui-icon name="shield-check" />
                        <span>{{ __('join.hero.note') }}</span>
                    </p>
                </div>

                <div class="join-noticeboard" aria-label="{{ __('join.preview.label') }}">
                    <div class="join-noticeboard__header">
                        <span>{{ __('join.preview.status') }}</span>
                        <strong>{{ __('join.preview.status_value') }}</strong>
                    </div>

                    <article class="join-update join-update--profile">
                        <span class="join-update__icon" aria-hidden="true"><x-ui-icon name="paw-print" /></span>
                        <div>
                            <p>{{ __('join.preview.profile_label') }}</p>
                            <strong>{{ __('join.preview.profile_text') }}</strong>
                        </div>
                    </article>

                    <article class="join-update join-update--place">
                        <span class="join-update__icon" aria-hidden="true"><x-ui-icon name="map-pinned" /></span>
                        <div>
                            <p>{{ __('join.preview.place_label') }}</p>
                            <strong>{{ __('join.preview.place_text') }}</strong>
                        </div>
                    </article>

                    <article class="join-update join-update--help">
                        <span class="join-update__icon" aria-hidden="true"><x-ui-icon name="siren" /></span>
                        <div>
                            <p>{{ __('join.preview.help_label') }}</p>
                            <strong>{{ __('join.preview.help_text') }}</strong>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section id="join-possibilities" class="join-section join-outcomes" data-join-section aria-labelledby="join-outcomes-title">
            <div class="join-container">
                <div class="join-section-heading">
                    <p class="join-eyebrow">{{ __('join.outcomes.eyebrow') }}</p>
                    <h2 id="join-outcomes-title">{{ __('join.outcomes.title') }}</h2>
                    <p>{{ __('join.outcomes.description') }}</p>
                </div>

                <div class="join-card-grid join-card-grid--three">
                    @forelse ($outcomes as $outcome)
                        <article class="join-feature-card">
                            <span class="join-feature-card__icon" aria-hidden="true">
                                <x-ui-icon :name="$outcome['icon']" />
                            </span>
                            <h3>{{ $outcome['title'] }}</h3>
                            <p>{{ $outcome['description'] }}</p>
                        </article>
                    @empty
                        <p>{{ __('join.outcomes.description') }}</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="join-section join-steps" data-join-section aria-labelledby="join-steps-title">
            <div class="join-container join-steps__layout">
                <div class="join-section-heading join-section-heading--sticky">
                    <p class="join-eyebrow">{{ __('join.steps.eyebrow') }}</p>
                    <h2 id="join-steps-title">{{ __('join.steps.title') }}</h2>
                </div>

                <ol class="join-step-list">
                    @forelse ($steps as $step)
                        <li>
                            <span class="join-step-list__number" aria-hidden="true">{{ $step['number'] }}</span>
                            <div>
                                <h3>{{ $step['title'] }}</h3>
                                <p>{{ $step['description'] }}</p>
                            </div>
                        </li>
                    @empty
                        <li>{{ __('join.steps.title') }}</li>
                    @endforelse
                </ol>
            </div>
        </section>

        <section class="join-section join-tools" data-join-section aria-labelledby="join-tools-title">
            <div class="join-container">
                <div class="join-section-heading join-section-heading--wide">
                    <p class="join-eyebrow">{{ __('join.tools.eyebrow') }}</p>
                    <h2 id="join-tools-title">{{ __('join.tools.title') }}</h2>
                    <p>{{ __('join.tools.description') }}</p>
                </div>

                <div class="join-card-grid join-card-grid--tools">
                    @forelse ($tools as $tool)
                        <article class="join-tool-card">
                            <span class="join-tool-card__icon" aria-hidden="true">
                                <x-ui-icon :name="$tool['icon']" />
                            </span>
                            <div>
                                <h3>{{ $tool['title'] }}</h3>
                                <p>{{ $tool['description'] }}</p>
                            </div>
                        </article>
                    @empty
                        <p>{{ __('join.tools.description') }}</p>
                    @endforelse
                </div>

                <div class="join-tools__actions">
                    <a href="{{ $explore_url }}" class="join-button join-button--secondary">
                        <x-ui-icon name="compass" />
                        <span>{{ __('join.tools.browse_action') }}</span>
                    </a>
                    <a href="{{ $forum_url }}" class="join-text-link">
                        {{ __('join.tools.forum_action') }}
                        <x-ui-icon name="arrow-right" />
                    </a>
                </div>
            </div>
        </section>

        <section class="join-section join-privacy" data-join-section aria-labelledby="join-privacy-title">
            <div class="join-container join-privacy__grid">
                <div class="join-privacy__copy">
                    <p class="join-eyebrow">{{ __('join.privacy.eyebrow') }}</p>
                    <h2 id="join-privacy-title">{{ __('join.privacy.title') }}</h2>
                    <p>{{ __('join.privacy.description') }}</p>

                    <ul class="join-check-list">
                        @forelse ($privacy_points as $privacyPoint)
                            <li>
                                <x-ui-icon name="check" />
                                <span>{{ $privacyPoint }}</span>
                            </li>
                        @empty
                            <li>{{ __('join.privacy.description') }}</li>
                        @endforelse
                    </ul>
                </div>

                <div class="join-privacy-panel" aria-label="{{ __('join.privacy.panel_label') }}">
                    <div>
                        <span><x-ui-icon name="eye" />{{ __('join.privacy.public_label') }}</span>
                        <strong>{{ __('join.privacy.public_value') }}</strong>
                    </div>
                    <div>
                        <span><x-ui-icon name="lock-keyhole" />{{ __('join.privacy.private_label') }}</span>
                        <strong>{{ __('join.privacy.private_value') }}</strong>
                    </div>
                    <div>
                        <span><x-ui-icon name="key-round" />{{ __('join.privacy.access_label') }}</span>
                        <strong>{{ __('join.privacy.access_value') }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="join-section join-faq" data-join-section aria-labelledby="join-faq-title">
            <div class="join-container join-faq__grid">
                <div class="join-section-heading join-section-heading--sticky">
                    <p class="join-eyebrow">{{ __('join.faq.eyebrow') }}</p>
                    <h2 id="join-faq-title">{{ __('join.faq.title') }}</h2>
                </div>

                <div class="join-faq__list">
                    @forelse ($faqs as $faq)
                        <details>
                            <summary>
                                <span>{{ $faq['question'] }}</span>
                                <x-ui-icon name="plus" />
                            </summary>
                            <p>{{ $faq['answer'] }}</p>
                        </details>
                    @empty
                        <p>{{ __('join.faq.title') }}</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="join-section join-final" data-join-section aria-labelledby="join-final-title">
            <div class="join-container join-final__panel">
                <div>
                    <p class="join-eyebrow">{{ __('join.final.eyebrow') }}</p>
                    <h2 id="join-final-title">{{ __('join.final.title') }}</h2>
                    <p>{{ __('join.final.description') }}</p>
                </div>
                <div class="join-final__actions">
                    <a href="{{ $register_url }}" class="join-button join-button--light" data-join-primary>
                        {{ __('join.final.primary_action') }}
                        <x-ui-icon name="arrow-up-right" />
                    </a>
                    <p>
                        {{ __('join.final.sign_in_prefix') }}
                        <a href="{{ $login_url }}" class="inline-flex items-center gap-1">
                            <x-ui-icon name="log-in" size="xs" />
                            <span>{{ __('join.final.sign_in_action') }}</span>
                        </a>
                    </p>
                </div>
            </div>
        </section>
    </main>

    <footer class="join-footer">
        <div class="join-container join-footer__inner">
            <div>
                <a href="{{ $canonical_url }}" class="join-brand">
                    <span class="join-brand__mark" aria-hidden="true"><x-ui-icon name="paw-print" /></span>
                    <span>{{ __('auth.brand') }}</span>
                </a>
                <p>{{ __('join.footer.tagline') }}</p>
            </div>
            <nav aria-label="{{ __('join.footer.navigation_label') }}">
                <a href="{{ $explore_url }}">{{ __('join.footer.explore') }}</a>
                <a href="{{ $forum_url }}">{{ __('join.footer.forum') }}</a>
                <a href="{{ $places_url }}">{{ __('join.footer.places') }}</a>
            </nav>
            <div class="join-footer__locale">
                <span>{{ __('join.footer.language_label') }}</span>
                <div role="group" aria-label="{{ __('join.footer.language_options_label') }}">
                    @forelse ($locale_options as $locale => $localeLabel)
                        <form method="POST" action="{{ $locale_url }}">
                            @csrf
                            <button
                                type="submit"
                                name="locale"
                                value="{{ $locale }}"
                                @if ($current_locale === $locale) aria-current="true" @endif
                            >
                                {{ $localeLabel }}
                            </button>
                        </form>
                    @empty
                        <span>{{ $current_locale }}</span>
                    @endforelse
                </div>
            </div>
        </div>
    </footer>
</x-join-layout>
