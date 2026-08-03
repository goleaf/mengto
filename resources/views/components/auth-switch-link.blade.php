@props(['prompt', 'href', 'label', 'icon' => 'arrow-right'])

<p class="auth-switch">
    <span>{{ $prompt }}</span>
    <a href="{{ $href }}" class="auth-switch__link">
        <span>{{ $label }}</span>
        <x-ui-icon :name="$icon" size="xs" />
    </a>
</p>
