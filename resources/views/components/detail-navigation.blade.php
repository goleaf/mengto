@props([
    'href',
    'label',
    'ariaLabel' => null,
])

<nav
    aria-label="{{ $ariaLabel ?? __('navigation.detail_label') }}"
    data-detail-navigation
    {{ $attributes->class('detail-navigation') }}
>
    <x-text-link :href="$href" icon="arrow-left" variant="back">
        {{ $label }}
    </x-text-link>

    @if ($slot->isNotEmpty())
        {{ $slot }}
    @endif
</nav>
