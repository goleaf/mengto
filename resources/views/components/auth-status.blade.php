@props(['tone' => 'success'])

<div {{ $attributes->class(['auth-status', 'auth-status--'.$tone]) }}>
    <span class="auth-status__icon" aria-hidden="true">
        @if ($tone === 'danger')
            <x-lucide-wifi-off />
        @else
            <x-lucide-circle-check />
        @endif
    </span>
    <div class="auth-status__copy">{{ $slot }}</div>
</div>
