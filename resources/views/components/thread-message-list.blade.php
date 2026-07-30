@props(['messages', 'dateLabel'])

<div class="min-h-[30rem] bg-paw-paper/50 p-4 sm:p-5">
    <div class="flex items-center gap-3" aria-label="{{ $dateLabel }}">
        <span class="h-px flex-1 bg-paw-line"></span>
        <span class="text-xs font-semibold text-paw-muted">{{ $dateLabel }}</span>
        <span class="h-px flex-1 bg-paw-line"></span>
    </div>

    <div role="list" aria-label="Conversation messages" class="mt-5 grid gap-4">
        @forelse ($messages as $message)
            <x-message-bubble :message="$message" />
        @empty
            <p role="listitem" class="text-center text-sm text-paw-muted">No messages in this conversation.</p>
        @endforelse
    </div>
</div>
