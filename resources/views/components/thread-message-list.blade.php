@props(['messages', 'dateLabel'])

<div class="min-h-[30rem] bg-paw-paper/50 p-4 sm:p-5">
    <div class="flex items-center gap-3" aria-label="{{ $dateLabel }}">
        <span class="h-px flex-1 bg-paw-line"></span>
        <span class="text-xs font-semibold text-paw-muted">{{ $dateLabel }}</span>
        <span class="h-px flex-1 bg-paw-line"></span>
    </div>

    <div role="list" aria-label="{{ __('ui.conversation_messages_f5f8903fca') }}" class="mt-5 grid gap-4">
        @forelse ($messages as $message)
            <x-message-bubble :message="$message" />
        @empty
            <p role="listitem" class="text-center text-sm text-paw-muted">{{ __('ui.no_messages_in_this_conversation_1e56e882e6') }}</p>
        @endforelse
    </div>
</div>
