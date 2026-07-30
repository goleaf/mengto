@if ($endpoint)
    <x-action-form :action="$endpoint" :payload="$payload">
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
    </x-action-form>
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
