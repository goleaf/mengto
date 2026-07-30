<a
    href="{{ $resolvedHref }}"
    {{ $attributes->class([
        'text-link',
        'text-link--'.$variant,
    ]) }}
>
    @if ($icon)
        <x-dynamic-component :component="'lucide-'.$icon" class="icon icon--sm" aria-hidden="true" />
    @endif
    <span>{{ $slot }}</span>
</a>
