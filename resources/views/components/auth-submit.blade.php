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
    <x-lucide-arrow-up-right class="auth-button__icon" wire:loading.remove wire:target="{{ $target }}" aria-hidden="true" />
</button>
