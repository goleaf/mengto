@props(['message'])

<div role="status" aria-live="polite" {{ $attributes->class('feedback') }}>
    <x-lucide-circle-check class="icon icon--sm" aria-hidden="true" />
    <p>{{ $message }}</p>
</div>
