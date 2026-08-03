@props([
    'target',
    'label',
    'loadingLabel',
    'type' => 'submit',
    'variant' => 'primary',
])

<button
    type="{{ $type }}"
    wire:loading.attr="disabled"
    wire:target="{{ $target }}"
    {{ $attributes->class(['auth-button', 'auth-button--'.$variant]) }}
>
    <span wire:loading.remove wire:target="{{ $target }}">{{ $label }}</span>
    <span wire:loading wire:target="{{ $target }}">{{ $loadingLabel }}</span>
    <x-ui-icon name="arrow-up-right" wire:loading.remove wire:target="{{ $target }}" class="auth-button__icon" />
</button>
