<article
    role="listitem"
    {{ $attributes->class(['panel', 'panel--clip', 'flex h-full flex-col']) }}
>
    {{ $media }}

    <div class="flex flex-1 flex-col p-5">
        {{ $slot }}
    </div>
</article>
