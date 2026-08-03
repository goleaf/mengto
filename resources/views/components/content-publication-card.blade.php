@props(['publication', 'headingLevel' => 2, 'showFullBody' => false])

<article {{ $attributes->class(['panel overflow-hidden']) }}>
    <div class="p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-2 text-xs text-paw-muted">
            <span class="font-semibold text-paw-ink">{{ $publication['actor']['name'] }}</span>
            <span aria-hidden="true">·</span>
            <span>{{ $publication['actor']['type_label'] }}</span>
            <span aria-hidden="true">·</span>
            <span>{{ $publication['published_at'] }}</span>
        </div>

        <div class="mt-3 flex flex-wrap gap-2 text-xs">
            <span class="rounded-md border border-paw-line px-2 py-1">{{ $publication['type_label'] }}</span>
            <span class="rounded-md border border-paw-line px-2 py-1">
                <x-ui-icon name="users" size="xs" class="mr-1 inline" />
                {{ $publication['audience_label'] }}
            </span>
        </div>

        @if ($publication['title'])
            @if ((int) $headingLevel === 1)
                <h1 class="mt-4 text-2xl font-bold text-paw-ink">{{ $publication['title'] }}</h1>
            @else
                <h2 class="mt-4 text-xl font-bold text-paw-ink">
                    <a href="{{ $publication['url'] }}" class="hover:underline">{{ $publication['title'] }}</a>
                </h2>
            @endif
        @endif

        @if ($publication['summary'])
            <p class="mt-3 text-sm font-medium leading-6 text-paw-ink">{{ $publication['summary'] }}</p>
        @endif

        @if ($publication['body'])
            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-paw-ink">
                @if ($showFullBody)
                    {{ $publication['body'] }}
                @else
                    {{ $publication['excerpt'] }}
                @endif
            </p>
        @endif

        @unless ($showFullBody)
            <a href="{{ $publication['url'] }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-paw-leaf hover:underline">
                {{ __('content.feed.open_publication') }}
                <x-ui-icon name="arrow-right" size="sm" />
            </a>
        @endunless
    </div>
</article>
