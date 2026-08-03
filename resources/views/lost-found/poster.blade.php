<!DOCTYPE html>
<html lang="{{ $html_locale }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $page_title }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/scss/app.scss', 'resources/js/app.js'])
    </head>
    <body class="bg-paw-cream text-paw-ink">
        <main class="mx-auto grid min-h-screen max-w-4xl place-items-center p-4 sm:p-8">
            <article class="poster-sheet w-full overflow-hidden rounded-md border border-paw-line bg-white shadow-xl">
                <div class="bg-paw-coral px-5 py-4 text-center text-white">
                    <p class="text-4xl font-black uppercase sm:text-6xl">
                        {{ $poster['heading'] }}
                    </p>
                </div>

                <div class="grid gap-6 p-5 sm:p-8 md:grid-cols-[minmax(0,1.2fr)_minmax(16rem,.8fr)]">
                    <div>
                        @if ($search_case['cover_url'])
                            <img src="{{ $search_case['cover_url'] }}" alt="{{ $poster['image_alt'] }}" class="aspect-[4/3] w-full rounded-md object-cover">
                        @else
                            <div class="grid aspect-[4/3] place-items-center rounded-md bg-paw-mint">
                                <x-ui-icon size="hero" :name="$search_case['type_icon']" class="text-paw-leaf" />
                            </div>
                        @endif
                        <h1 class="mt-5 text-4xl font-black sm:text-5xl">{{ $search_case['pet_name'] }}</h1>
                        <p class="mt-2 text-xl font-semibold">{{ $poster['pet_summary'] }}</p>
                    </div>

                    <div class="grid content-start gap-5">
                        <div class="rounded-md border-2 border-paw-coral p-4">
                            <p class="text-xs font-bold uppercase text-paw-coral">{{ __('lost_found.poster.current_status') }}</p>
                            <p class="mt-1 text-2xl font-black">{{ $search_case['status_label'] }}</p>
                            <p class="mt-2 text-sm leading-6">{{ $search_case['latest_update'] }}</p>
                        </div>

                        <dl class="grid gap-3">
                            <div><dt class="text-xs font-bold uppercase text-paw-muted">{{ __('lost_found.poster.area') }}</dt><dd class="mt-1 text-lg font-bold">{{ $search_case['last_seen_area'] }}</dd></div>
                            <div><dt class="text-xs font-bold uppercase text-paw-muted">{{ __('lost_found.poster.when') }}</dt><dd class="mt-1 text-lg font-bold">{{ $search_case['last_seen_label'] }}</dd></div>
                            @if ($search_case['distinctive_marks'])<div><dt class="text-xs font-bold uppercase text-paw-muted">{{ __('lost_found.poster.identifying_marks') }}</dt><dd class="mt-1 font-semibold">{{ $search_case['distinctive_marks'] }}</dd></div>@endif
                        </dl>

                        <div class="grid grid-cols-[8rem_1fr] items-center gap-4 border-t border-paw-line pt-5">
                            <img src="{{ $qr_code }}" alt="{{ __('lost_found.poster.qr_alt') }}" class="size-32">
                            <div>
                                <p class="font-bold">{{ __('lost_found.poster.scan_status') }}</p>
                                <p class="mt-1 break-all text-xs text-paw-muted">{{ $public_url }}</p>
                                <p class="mt-2 text-sm font-semibold text-paw-leaf">{{ __('lost_found.poster.protected_form') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($search_case['urgent'])
                    <div class="border-t-4 border-paw-sun bg-paw-sun/30 px-5 py-4 text-center">
                        <p class="text-xl font-black">{{ __('lost_found.poster.do_not_chase') }}</p>
                        <p class="mt-1 font-semibold">{{ $search_case['approach_instructions'] }}</p>
                    </div>
                @endif
            </article>

            <div class="poster-controls mt-5 flex flex-wrap justify-center gap-3">
                <button type="button" data-print-page class="action action--primary">
                    <x-ui-icon name="printer" />
                    <span>{{ __('lost_found.poster.print') }}</span>
                </button>
                <a href="{{ route('lost-found.show', $search_case['slug']) }}" class="action action--surface">
                    <x-ui-icon name="arrow-left" />
                    <span>{{ __('lost_found.poster.back') }}</span>
                </a>
            </div>
        </main>
    </body>
</html>
