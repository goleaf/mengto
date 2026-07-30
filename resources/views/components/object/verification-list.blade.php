@props(['items', 'expires' => null])

<div class="grid gap-3">
    <div class="grid gap-2">
        @forelse ($items as $item)
            <div class="flex items-start gap-3 border-b border-paw-line pb-2 last:border-0">
                <x-dynamic-component
                    :component="$item['verified'] ? 'lucide-badge-check' : 'lucide-circle-help'"
                    class="mt-0.5 size-5 shrink-0 {{ $item['verified'] ? 'text-paw-leaf' : 'text-paw-muted' }}"
                    aria-hidden="true"
                />
                <div>
                    <p class="font-semibold">{{ $item['label'] }} · {{ $item['verified'] ? 'verified' : 'not verified' }}</p>
                    <p class="text-sm text-paw-muted">{{ $item['detail'] }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-paw-muted">No verification details are available.</p>
        @endforelse
    </div>

    @if ($expires)
        <p class="text-xs text-paw-muted">Current verification review period ends {{ $expires }}.</p>
    @endif
</div>
