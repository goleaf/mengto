@props([
    'label',
    'icon' => null,
    'variant' => 'surface',
    'size' => 'compact',
    'endpoint' => null,
    'payload' => [],
    'href' => null,
    'type' => 'button',
    'active' => false,
    'activeLabel' => null,
    'activeIcon' => null,
    'pressed' => null,
    'disabled' => false,
    'name' => null,
    'value' => null,
])

@php
    $resolvedLabel = $active && $activeLabel ? $activeLabel : $label;
    $resolvedIcon = $active && $activeIcon ? $activeIcon : $icon;
    $classes = [
        'action',
        'action--'.$variant,
        'action--'.$size,
        'action--active' => $active,
    ];
    $isDisabled = $disabled || ($endpoint === null && $href === null && $type === 'button');
@endphp

@if ($endpoint)
    <x-ui.action-form :action="$endpoint" :payload="$payload">
        <button
            type="submit"
            @if ($pressed !== null) aria-pressed="{{ $pressed ? 'true' : 'false' }}" @endif
            {{ $attributes->class($classes) }}
        >
            @if ($resolvedIcon)
                <x-dynamic-component :component="'lucide-'.$resolvedIcon" class="icon icon--sm" aria-hidden="true" />
            @endif
            <span>{{ $resolvedLabel }}</span>
        </button>
    </x-ui.action-form>
@elseif ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($resolvedIcon)
            <x-dynamic-component :component="'lucide-'.$resolvedIcon" class="icon icon--sm" aria-hidden="true" />
        @endif
        <span>{{ $resolvedLabel }}</span>
    </a>
@else
    <button
        type="{{ $type }}"
        @if ($name) name="{{ $name }}" @endif
        @if ($name && $value !== null) value="{{ $value }}" @endif
        @if ($pressed !== null) aria-pressed="{{ $pressed ? 'true' : 'false' }}" @endif
        @disabled($isDisabled)
        @if ($isDisabled) aria-disabled="true" @endif
        {{ $attributes->class($classes) }}
    >
        @if ($resolvedIcon)
            <x-dynamic-component :component="'lucide-'.$resolvedIcon" class="icon icon--sm" aria-hidden="true" />
        @endif
        <span>{{ $resolvedLabel }}</span>
    </button>
@endif
