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
    <x-dynamic-component :component="'lucide-'.$icon" class="icon icon--sm" aria-hidden="true" />
    <span>{{ $label }}</span>
</a>
