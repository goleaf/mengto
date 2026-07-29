@props([
    'initials',
    'tone' => 'sun',
    'size' => 'compact',
])

<span
    aria-hidden="true"
    {{ $attributes->class([
        'pc-initials-avatar',
        'pc-initials-avatar--regular' => $size === 'regular',
        'pc-initials-avatar--sun' => $tone === 'sun',
        'pc-initials-avatar--mint' => $tone === 'mint',
        'pc-initials-avatar--paper' => $tone === 'paper',
    ]) }}
>
    {{ $initials }}
</span>
