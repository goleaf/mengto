@props([
    'id',
    'label',
    'placeholder',
])

<div {{ $attributes->class('pc-field-wrap') }}>
    <label for="{{ $id }}" class="sr-only">{{ $label }}</label>
    <x-lucide-search class="pc-icon pc-icon--sm" aria-hidden="true" />
    <input
        id="{{ $id }}"
        type="search"
        placeholder="{{ $placeholder }}"
        aria-disabled="true"
        disabled
        class="pc-field pc-field--with-icon"
    >
</div>
