@props([
    'type',
    'tone' => 'surface',
    'size' => 'compact',
])

@php
    $icon = match (strtolower($type)) {
        'dog' => 'dog',
        'cat' => 'cat',
        'bird' => 'bird',
        'rabbit' => 'rabbit',
        default => 'paw-print',
    };
@endphp

<x-pet-social.status-badge
    :label="$type"
    :icon="$icon"
    :tone="$tone"
    :size="$size"
    {{ $attributes }}
/>
