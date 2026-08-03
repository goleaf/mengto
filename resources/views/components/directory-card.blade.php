<article
    role="listitem"
    data-ui-card
    {{ $attributes->class(['panel', 'panel--clip', 'flex h-full min-w-0 flex-col bg-white']) }}
>
    <div
        data-card-region="media"
        class="relative isolate min-w-0 shrink-0 overflow-hidden border-b border-paw-line bg-paw-paper"
    >
        {{ $media }}
    </div>

    <div data-card-region="body" class="relative isolate flex min-w-0 flex-1 flex-col bg-white p-5">
        {{ $slot }}

        @isset($footer)
            <footer data-card-region="footer" class="mt-auto min-w-0 border-t border-paw-line pt-5">
                {{ $footer }}
            </footer>
        @endisset
    </div>
</article>
