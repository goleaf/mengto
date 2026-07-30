@props([
    'value',
    'label',
    'detail' => null,
])

<div {{ $attributes->class(['progress']) }}>
    <div class="progress__header">
        <span class="progress__label">{{ $label }}</span>
        <span class="progress__value">{{ $value }}%</span>
    </div>
    <progress
        value="{{ $value }}"
        max="100"
        aria-label="{{ $label }}"
        class="progress__track"
    >{{ $value }}%</progress>
    @if ($detail)
        <p class="progress__detail">{{ $detail }}</p>
    @endif
</div>
