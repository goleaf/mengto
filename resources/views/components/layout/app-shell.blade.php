@props(['owner', 'title' => 'PawCircle', 'activeSection' => 'feed'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

        <title>{{ $title }}</title>

        <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
        <link rel="dns-prefetch" href="//images.unsplash.com">

        @fonts
        @vite(['resources/css/app.css', 'resources/scss/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <a href="#main-content" class="sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:not-sr-only focus:rounded-md focus:bg-paw-ink focus:px-4 focus:py-3 focus:text-sm focus:font-semibold focus:text-white focus:outline-none focus:ring-2 focus:ring-paw-leaf focus:ring-offset-2">
            Skip to content
        </a>

        <div class="min-h-screen bg-paw-cream text-paw-ink">
            <x-layout.site-header :owner="$owner" :active-section="$activeSection" />

            <main id="main-content" tabindex="-1" class="app-main mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 focus:outline-none">
                @if (session('pawcircle.feedback'))
                    <x-ui.flash-feedback :message="session('pawcircle.feedback')" class="mb-4" />
                @endif

                {{ $slot }}
            </main>

            <x-layout.primary-navigation :active-section="$activeSection" variant="mobile" />
        </div>
    </body>
</html>
