@props(['context'])

<div class="flex items-center gap-3 border-b border-paw-line bg-paw-paper px-4 py-3 sm:px-5">
    <img
        src="{{ $context['image'] }}"
        alt="{{ $context['image_alt'] }}"
        width="56"
        height="56"
        loading="lazy"
        decoding="async"
        class="size-14 shrink-0 rounded-md object-cover"
    >

    <div class="min-w-0">
        <p class="text-xs font-semibold text-paw-leaf">{{ $context['eyebrow'] }}</p>
        <h3 class="mt-1 text-sm font-semibold text-paw-ink">{{ $context['title'] }}</h3>
        <p class="mt-1 text-xs text-paw-muted">{{ $context['detail'] }}</p>
    </div>
</div>
