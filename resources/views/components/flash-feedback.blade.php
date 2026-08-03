@props(['message'])

<div role="status" aria-live="polite" {{ $attributes->class('feedback') }}>
    <x-ui-icon name="circle-check" size="sm" />
    <p>{{ $message }}</p>
</div>
