@props(['documents'])

<div {{ $attributes->class(['medical-document-list']) }}>
    @forelse ($documents as $document)
        <article class="medical-document-list__item">
            <span class="medical-document-list__icon">
                <x-lucide-file-heart class="size-5" aria-hidden="true" />
            </span>
            <div class="min-w-0">
                <h3 class="font-bold">{{ $document['title'] }}</h3>
                <p class="mt-1 text-xs text-paw-muted">
                    {{ $document['type'] }} · {{ $document['size'] }} · {{ $document['created_at'] }}
                </p>
                <p class="mt-1 text-xs font-semibold text-paw-muted">{{ $document['source_name'] }} · {{ $document['verification'] }}</p>
            </div>
            @if ($document['download_url'])
                <a href="{{ $document['download_url'] }}" class="action action--surface action--compact" title="{{ __('presentation.download_document', ['title' => $document['title']]) }}">
                    <x-lucide-download class="icon icon--sm" aria-hidden="true" />
                    <span class="sr-only">{{ __('presentation.download_document', ['title' => $document['title']]) }}</span>
                </a>
            @else
                <x-status-badge label="{{ __('ui.view_only_9b4c6c8590') }}" icon="eye" tone="surface" />
            @endif
        </article>
    @empty
        <div class="medical-empty">
            <x-lucide-file-x class="size-7" aria-hidden="true" />
            <p>{{ __('ui.no_documents_in_this_view_38f3bf316f') }}</p>
        </div>
    @endforelse
</div>
