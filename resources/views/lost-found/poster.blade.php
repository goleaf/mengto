<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $page_title }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/scss/app.scss'])
        <style>
            @media print {
                .poster-controls { display: none !important; }
                body { background: white !important; }
                .poster-sheet { border: 0 !important; box-shadow: none !important; margin: 0 !important; max-width: none !important; }
            }
        </style>
    </head>
    <body class="bg-paw-cream text-paw-ink">
        <main class="mx-auto grid min-h-screen max-w-4xl place-items-center p-4 sm:p-8">
            <article class="poster-sheet w-full overflow-hidden rounded-md border border-paw-line bg-white shadow-xl">
                <div class="bg-paw-coral px-5 py-4 text-center text-white">
                    <p class="text-4xl font-black uppercase sm:text-6xl">
                        {{ $search_case['status'] === 'returned' || $search_case['status'] === 'self-returned' ? 'Found' : ($search_case['type'] === 'lost' ? 'Missing' : 'Found animal') }}
                    </p>
                </div>

                <div class="grid gap-6 p-5 sm:p-8 md:grid-cols-[minmax(0,1.2fr)_minmax(16rem,.8fr)]">
                    <div>
                        @if ($search_case['cover_url'])
                            <img src="{{ $search_case['cover_url'] }}" alt="{{ $search_case['pet_name'] }}, {{ strtolower($search_case['species_label']) }}, {{ $search_case['color'] }}" class="aspect-[4/3] w-full rounded-md object-cover">
                        @else
                            <div class="grid aspect-[4/3] place-items-center rounded-md bg-paw-mint">
                                <x-dynamic-component :component="'lucide-'.$search_case['type_icon']" class="size-24 text-paw-leaf" aria-hidden="true" />
                            </div>
                        @endif
                        <h1 class="mt-5 text-4xl font-black sm:text-5xl">{{ $search_case['pet_name'] }}</h1>
                        <p class="mt-2 text-xl font-semibold">{{ $search_case['species_label'] }} · {{ $search_case['breed'] ?: 'breed unknown' }} · {{ $search_case['color'] }}</p>
                    </div>

                    <div class="grid content-start gap-5">
                        <div class="rounded-md border-2 border-paw-coral p-4">
                            <p class="text-xs font-bold uppercase text-paw-coral">Current status</p>
                            <p class="mt-1 text-2xl font-black">{{ $search_case['status_label'] }}</p>
                            <p class="mt-2 text-sm leading-6">{{ $search_case['latest_update'] }}</p>
                        </div>

                        <dl class="grid gap-3">
                            <div><dt class="text-xs font-bold uppercase text-paw-muted">Area</dt><dd class="mt-1 text-lg font-bold">{{ $search_case['last_seen_area'] }}</dd></div>
                            <div><dt class="text-xs font-bold uppercase text-paw-muted">When</dt><dd class="mt-1 text-lg font-bold">{{ $search_case['last_seen_label'] }}</dd></div>
                            @if ($search_case['distinctive_marks'])<div><dt class="text-xs font-bold uppercase text-paw-muted">Identifying marks</dt><dd class="mt-1 font-semibold">{{ $search_case['distinctive_marks'] }}</dd></div>@endif
                        </dl>

                        <div class="grid grid-cols-[8rem_1fr] items-center gap-4 border-t border-paw-line pt-5">
                            <img src="{{ $qr_code }}" alt="QR code to the current search report" class="size-32">
                            <div>
                                <p class="font-bold">Scan for current status</p>
                                <p class="mt-1 break-all text-xs text-paw-muted">{{ $public_url }}</p>
                                <p class="mt-2 text-sm font-semibold text-paw-leaf">Send a sighting through the protected form.</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($search_case['urgent'])
                    <div class="border-t-4 border-paw-sun bg-paw-sun/30 px-5 py-4 text-center">
                        <p class="text-xl font-black">Do not chase, shout, or surround.</p>
                        <p class="mt-1 font-semibold">{{ $search_case['approach_instructions'] }}</p>
                    </div>
                @endif
            </article>

            <div class="poster-controls mt-5 flex flex-wrap justify-center gap-3">
                <button type="button" onclick="window.print()" class="action action--primary">
                    <x-lucide-printer class="icon" aria-hidden="true" />
                    <span>Print poster</span>
                </button>
                <a href="{{ route('lost-found.show', $search_case['slug']) }}" class="action action--surface">
                    <x-lucide-arrow-left class="icon" aria-hidden="true" />
                    <span>Back to report</span>
                </a>
            </div>
        </main>
    </body>
</html>
