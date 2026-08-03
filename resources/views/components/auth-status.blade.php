@props(['tone' => 'success'])

<div {{ $attributes->class(['auth-status', 'auth-status--'.$tone]) }}>
    <span class="auth-status__icon" aria-hidden="true">
        @if ($tone === 'danger')
            <x-ui-icon name="wifi-off" />
        @else
            <x-ui-icon name="circle-check" />
        @endif
    </span>
    <div class="auth-status__copy">{{ $slot }}</div>
</div>
