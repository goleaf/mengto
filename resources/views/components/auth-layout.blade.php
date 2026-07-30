<!DOCTYPE html>
<html lang="{{ $htmlLocale }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title }} · {{ __('auth.brand') }}</title>

        @fonts
        @vite(['resources/css/app.css'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-paw-cream text-paw-ink antialiased">
        <a
            href="#main-content"
            class="sr-only focus:fixed focus:start-4 focus:top-4 focus:z-50 focus:not-sr-only focus:rounded-md focus:bg-paw-ink focus:px-4 focus:py-3 focus:text-sm focus:font-semibold focus:text-white focus:outline-none focus:ring-2 focus:ring-paw-leaf focus:ring-offset-2"
        >
            {{ __('auth.accessibility.skip_to_content') }}
        </a>

        <main id="main-content" class="grid min-h-dvh place-items-center px-4 py-8 sm:px-6" tabindex="-1">
            <div class="w-full max-w-md">
                <a
                    href="{{ route('home') }}"
                    class="mb-8 inline-flex items-center gap-3 text-xl font-semibold text-paw-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-4"
                    wire:navigate
                >
                    <span aria-hidden="true" class="grid size-10 place-items-center rounded-md bg-paw-leaf text-white">{{ __('ui.p_5c62e091b8') }}</span>
                    <span>{{ __('auth.brand') }}</span>
                </a>

                <section class="rounded-lg border border-paw-line bg-paw-paper p-6 shadow-sm sm:p-8">
                    <p
                        wire:offline
                        role="status"
                        aria-live="polite"
                        class="mb-5 rounded-md border border-paw-coral bg-paw-cream px-4 py-3 text-sm font-medium"
                    >
                        {{ __('auth.connection.offline') }}
                    </p>

                    {{ $slot }}
                </section>
            </div>
        </main>

        @livewireScripts
    </body>
</html>
