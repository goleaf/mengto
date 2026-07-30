@if ($endpoint)
    <x-action-form :action="$endpoint" :payload="$payload">
        <button
            type="submit"
            aria-label="{{ $resolvedLabel }}"
            title="{{ $resolvedLabel }}"
            aria-pressed="{{ $active ? 'true' : 'false' }}"
            {{ $attributes->class(['feed-action', 'feed-action--active' => $active]) }}
        >
            <x-dynamic-component :component="'lucide-'.$icon" class="icon icon--sm" aria-hidden="true" />
            <span class="feed-action__label">{{ $resolvedLabel }}</span>
            @if ($compactLabel !== null)
                <span class="feed-action__compact-label" aria-hidden="true">{{ $compactLabel }}</span>
            @endif
        </button>
    </x-action-form>
@elseif ($href)
    <a href="{{ $href }}" aria-label="{{ $resolvedLabel }}" title="{{ $resolvedLabel }}" {{ $attributes->class('feed-action') }}>
        <x-dynamic-component :component="'lucide-'.$icon" class="icon icon--sm" aria-hidden="true" />
        <span class="feed-action__label">{{ $resolvedLabel }}</span>
        @if ($compactLabel !== null)
            <span class="feed-action__compact-label" aria-hidden="true">{{ $compactLabel }}</span>
        @endif
    </a>
@endif
