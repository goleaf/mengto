<!DOCTYPE html>
<html lang="{{ $document_locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $article['title'] }}</title>
    @vite(['resources/css/app.css', 'resources/scss/app.scss'])
</head>
<body class="bg-white text-paw-ink">
    <main class="mx-auto max-w-3xl px-6 py-10 print:max-w-none print:p-0">
        <header class="border-b border-paw-line pb-6">
            <p class="text-sm font-semibold">{{ $article['status_label'] }}</p>
            <h1 class="mt-2 text-4xl font-bold">{{ $article['title'] }}</h1>
            <p class="mt-4 text-lg">{{ $article['summary'] }}</p>
        </header>

        <article class="whitespace-pre-line py-8 leading-7">{{ $article['body'] }}</article>

        <dl class="grid gap-2 border-t border-paw-line py-6 text-sm">
            <div><dt class="font-semibold">{{ __('knowledge.fields.language') }}</dt><dd>{{ $article['language'] }}</dd></div>
            @if ($article['jurisdiction'])
                <div><dt class="font-semibold">{{ __('knowledge.fields.jurisdiction') }}</dt><dd>{{ $article['jurisdiction'] }}</dd></div>
            @endif
            @if ($article['taxon'])
                <div><dt class="font-semibold">{{ __('knowledge.fields.taxon') }}</dt><dd>{{ $article['taxon']['scientific_name'] }}</dd></div>
            @endif
            <div><dt class="font-semibold">{{ __('knowledge.editor.current_version', ['version' => $article['version']]) }}</dt><dd>{{ $article['reviewed_label'] }}</dd></div>
        </dl>

        @if ($article['sources'] !== [])
            <section aria-labelledby="print-guide-sources-heading">
                <h2 id="print-guide-sources-heading" class="text-xl font-bold">{{ __('knowledge.export.sources_heading') }}</h2>
                <ol class="mt-3 list-decimal space-y-2 ps-5">
                    @forelse ($article['sources'] as $source)
                        <li><a href="{{ $source['url'] }}" rel="noreferrer">{{ $source['url'] }}</a></li>
                    @empty
                        <li>{{ __('knowledge.empty.sources') }}</li>
                    @endforelse
                </ol>
            </section>
        @endif
    </main>
</body>
</html>
