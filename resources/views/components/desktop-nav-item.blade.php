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
    {{ $attributes->class('desktop-nav__item') }}
>
    <x-ui-icon :name="$icon" size="xs" />
    <span>{{ $label }}</span>
</a>
