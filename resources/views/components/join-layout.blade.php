@props([
    'pageTitle',
    'metaDescription',
    'canonicalUrl',
    'htmlLocale',
    'registerUrl',
    'loginUrl',
])

<!DOCTYPE html>
<html lang="{{ $htmlLocale }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="description" content="{{ $metaDescription }}">
        <meta name="theme-color" content="#f7f4ed">

        <title>{{ $pageTitle }}</title>
        <link rel="canonical" href="{{ $canonicalUrl }}">

        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">

        @fonts
        @vite(['resources/css/app.css', 'resources/scss/app.scss'])
    </head>
    <body class="join-body antialiased">
        <a href="#main-content" class="join-skip-link sr-only focus:not-sr-only">
            {{ __('join.accessibility.skip_to_content') }}
        </a>

        <header class="join-header">
            <div class="join-container join-header__inner">
                <a href="{{ $canonicalUrl }}" class="join-brand" aria-label="{{ __('auth.brand') }}">
                    <span class="join-brand__mark" aria-hidden="true">
                        <x-ui-icon name="paw-print" />
                    </span>
                    <span>{{ __('auth.brand') }}</span>
                </a>

                <nav class="join-header__actions" aria-label="{{ __('join.header.navigation_label') }}">
                    <a href="{{ $loginUrl }}" class="join-link-button">
                        <x-ui-icon name="log-in" size="sm" />
                        <span>{{ __('join.header.sign_in') }}</span>
                    </a>
                    <a href="{{ $registerUrl }}" class="join-button join-button--primary" data-join-primary>
                        <x-ui-icon name="user-plus" size="sm" />
                        <span>{{ __('join.header.create_profile') }}</span>
                    </a>
                </nav>
            </div>
        </header>

        {{ $slot }}
    </body>
</html>
