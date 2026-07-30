@props([
    'initials',
    'tone' => 'sun',
    'size' => 'compact',
])

<span
    aria-hidden="true"
    {{ $attributes->class([
        'initials-avatar',
        'initials-avatar--regular' => $size === 'regular',
        'initials-avatar--sun' => $tone === 'sun',
        'initials-avatar--mint' => $tone === 'mint',
        'initials-avatar--paper' => $tone === 'paper',
    ]) }}
>
    {{ $initials }}
</span>
