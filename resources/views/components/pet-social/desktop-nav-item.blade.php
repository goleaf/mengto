@props([
    'href',
    'label',
    'name',
    'active' => false,
])

<a
    href="{{ $href }}"
    data-nav-item="{{ $name }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class('pc-desktop-nav__item') }}
>
    {{ $label }}
</a>
