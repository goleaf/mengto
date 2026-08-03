<!DOCTYPE html>
<html lang="{{ $htmlLocale }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title }} · {{ __('auth.brand') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/scss/app.scss'])
        @livewireStyles
    </head>
    <body class="auth-body antialiased">
        <a
            href="#main-content"
            class="auth-skip-link sr-only focus:not-sr-only"
        >
            {{ __('auth.accessibility.skip_to_content') }}
        </a>

        <main id="main-content" class="auth-shell" data-auth-shell tabindex="-1">
            <aside class="auth-story" aria-label="{{ __('auth.shell.landmark') }}">
                <a
                    href="{{ route('home') }}"
                    class="auth-brand"
                >
                    <span class="auth-brand__mark" aria-hidden="true">
                        <x-ui-icon name="paw-print" />
                    </span>
                    <span class="auth-brand__name">{{ __('auth.brand') }}</span>
                </a>

                <div class="auth-story__content">
                    <p class="auth-story__eyebrow">{{ __('auth.shell.eyebrow') }}</p>
                    <p class="auth-story__title">{{ __('auth.shell.title') }}</p>
                    <p class="auth-story__description">{{ __('auth.shell.description') }}</p>

                    <ul class="auth-story__benefits" aria-label="{{ __('auth.shell.benefits_label') }}">
                        <li class="auth-benefit">
                            <span class="auth-benefit__icon" aria-hidden="true"><x-ui-icon name="shield-check" /></span>
                            <span>
                                <strong>{{ __('auth.shell.privacy_title') }}</strong>
                                <small>{{ __('auth.shell.privacy_description') }}</small>
                            </span>
                        </li>
                        <li class="auth-benefit">
                            <span class="auth-benefit__icon" aria-hidden="true"><x-ui-icon name="heart-handshake" /></span>
                            <span>
                                <strong>{{ __('auth.shell.care_title') }}</strong>
                                <small>{{ __('auth.shell.care_description') }}</small>
                            </span>
                        </li>
                        <li class="auth-benefit">
                            <span class="auth-benefit__icon" aria-hidden="true"><x-ui-icon name="map-pinned" /></span>
                            <span>
                                <strong>{{ __('auth.shell.community_title') }}</strong>
                                <small>{{ __('auth.shell.community_description') }}</small>
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="auth-story__footer">
                    <x-ui-icon name="lock-keyhole" />
                    <span>{{ __('auth.shell.footer') }}</span>
                </div>

                @if ($supportedLocales !== [])
                    <nav class="auth-locale" aria-label="{{ __('auth.language_selector') }}">
                        @forelse ($supportedLocales as $locale => $label)
                            <form method="POST" action="{{ route('locale.update') }}">
                                @csrf
                                <button
                                    type="submit"
                                    name="locale"
                                    value="{{ $locale }}"
                                    @if ($activeLocale === $locale) aria-current="true" @endif
                                >
                                    {{ $label }}
                                </button>
                            </form>
                        @empty
                        @endforelse
                    </nav>
                @endif

                <x-ui-icon name="paw-print" class="auth-story__watermark" />
            </aside>

            <section class="auth-workspace" aria-label="{{ __('auth.shell.form_landmark') }}">
                <div class="auth-card">
                    <x-auth-status
                        wire:offline
                        role="status"
                        aria-live="polite"
                        tone="danger"
                        class="auth-card__offline"
                    >
                        {{ __('auth.connection.offline') }}
                    </x-auth-status>

                    {{ $slot }}
                </div>
            </section>
        </main>

        @livewireScripts
    </body>
</html>
