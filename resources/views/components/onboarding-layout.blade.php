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
    <body class="min-h-screen bg-paw-cream text-paw-ink antialiased">
        <a
            href="#onboarding-main"
            class="sr-only fixed left-4 top-4 z-50 rounded-md bg-paw-ink px-4 py-3 font-semibold text-white focus:not-sr-only"
        >
            {{ __('onboarding.accessibility.skip_to_content') }}
        </a>

        <main id="onboarding-main" class="mx-auto flex min-h-screen w-full max-w-3xl flex-col px-4 py-6 sm:px-6 sm:py-10" tabindex="-1">
            <div class="mb-6 flex items-center gap-3">
                <span class="flex size-11 items-center justify-center rounded-full bg-paw-leaf text-white" aria-hidden="true">
                    <x-ui-icon name="paw-print" />
                </span>
                <span class="text-lg font-bold">{{ __('auth.brand') }}</span>
            </div>

            @if (session('feedback'))
                <x-flash-feedback :message="session('feedback')" class="mb-4" />
            @endif

            <div class="rounded-lg border border-paw-line bg-paw-paper p-5 sm:p-8">
                {{ $slot }}
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm text-paw-muted">
                <p>{{ __('onboarding.page.resume_note') }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="min-h-11 rounded-md px-4 font-semibold text-paw-ink underline decoration-paw-line underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-paw-leaf">
                        {{ __('onboarding.page.logout') }}
                    </button>
                </form>
            </div>
        </main>

        @livewireScripts
    </body>
</html>
