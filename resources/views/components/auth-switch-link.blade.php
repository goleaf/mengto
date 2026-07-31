@props(['prompt', 'href', 'label'])

<p class="auth-switch">
    <span>{{ $prompt }}</span>
    <a href="{{ $href }}" wire:navigate class="auth-switch__link">{{ $label }}</a>
</p>
