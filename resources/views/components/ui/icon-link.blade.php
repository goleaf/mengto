@props([
    'href',
    'label',
    'icon',
    'name',
    'active' => false,
])

<a
    href="{{ $href }}"
    aria-label="{{ $label }}"
    title="{{ $label }}"
    data-header-link="{{ $name }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class('header-icon') }}
>
    <x-dynamic-component :component="'lucide-'.$icon" class="icon" aria-hidden="true" />
</a>
