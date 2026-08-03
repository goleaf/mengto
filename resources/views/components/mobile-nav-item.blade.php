@props([
    'href',
    'label',
    'icon',
    'name',
    'active' => false,
])

<a
    href="{{ $href }}"
    data-nav-item="{{ $name }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class('mobile-nav__item') }}
>
    <x-ui-icon size="sm" :name="$icon" />
    <span>{{ $label }}</span>
</a>
