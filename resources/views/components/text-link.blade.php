<a
    href="{{ $resolvedHref }}"
    {{ $attributes->class([
        'text-link',
        'text-link--'.$variant,
    ]) }}
>
    @if ($icon)
        <x-ui-icon size="sm" :name="$icon" />
    @endif
    <span>{{ $slot }}</span>
</a>
