@props(['icon', 'label', 'value', 'datetime' => null])

<div class="walk-meta__item">
    <x-dynamic-component :component="'lucide-'.$icon" class="walk-meta__icon icon" aria-hidden="true" />
    <div class="walk-meta__content">
        <dt class="walk-meta__label">{{ $label }}</dt>
        <dd class="walk-meta__value">
            @if ($datetime)
                <time datetime="{{ $datetime }}">{{ $value }}</time>
            @else
                {{ $value }}
            @endif
        </dd>
    </div>
</div>
