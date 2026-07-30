@props([
    'id',
    'label',
    'placeholder',
    'name' => 'q',
    'value' => '',
])

<div {{ $attributes->class('field-wrap') }}>
    <label for="{{ $id }}" class="sr-only">{{ $label }}</label>
    <x-lucide-search class="icon icon--sm" aria-hidden="true" />
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="search"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        class="field field--with-icon"
    >
</div>
