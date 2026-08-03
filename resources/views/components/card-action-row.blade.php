@props(['fill' => false])

<div
    data-card-action-row
    {{ $attributes->class([
        'card-action-row',
        'card-action-row--fill' => $fill,
    ]) }}
>
    {{ $slot }}
</div>
