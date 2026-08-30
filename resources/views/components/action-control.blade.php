@if ($endpoint)
    <x-action-form :action="$endpoint" :payload="$payload">
        <button
            type="submit"
            data-action-submit
            @if ($pressed !== null) aria-pressed="{{ $pressed ? 'true' : 'false' }}" @endif
            @disabled($isDisabled)
            @if ($isDisabled) aria-disabled="true" @endif
            {{ $attributes->class($classes) }}
        >
            @if ($resolvedIcon)
                <x-ui-icon size="sm" :name="$resolvedIcon" />
            @endif
            <span data-action-label>{{ $resolvedLabel }}</span>
            <span data-action-loading-label hidden>{{ $resolvedLoadingLabel }}</span>
        </button>
    </x-action-form>
@elseif ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($resolvedIcon)
            <x-ui-icon size="sm" :name="$resolvedIcon" />
        @endif
        <span data-action-label>{{ $resolvedLabel }}</span>
    </a>
@else
    <button
        type="{{ $type }}"
        data-action-submit
        @if ($name) name="{{ $name }}" @endif
        @if ($name && $value !== null) value="{{ $value }}" @endif
        @if ($pressed !== null) aria-pressed="{{ $pressed ? 'true' : 'false' }}" @endif
        @disabled($isDisabled)
        @if ($isDisabled) aria-disabled="true" @endif
        {{ $attributes->class($classes) }}
    >
        @if ($resolvedIcon)
            <x-ui-icon size="sm" :name="$resolvedIcon" />
        @endif
        <span data-action-label>{{ $resolvedLabel }}</span>
    </button>
@endif
