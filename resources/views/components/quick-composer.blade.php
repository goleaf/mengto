@props(['href', 'draftCount' => 0])

<section class="quick-composer" aria-label="{{ __('ui.create_a_publication_08c3ee1c2e') }}">
    <div class="quick-composer__prompt">
        <x-avatar
            src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&crop=faces&w=96&h=96&q=80"
            alt="Mia Carter"
            size="header"
        />
        <a href="{{ $href }}" class="quick-composer__input">{{ __('ui.share_something_useful_with_your_circle_0ec157a607') }}</a>
    </div>

    <div class="quick-composer__tools">
        <a href="{{ $href }}" class="quick-composer__tool">
            <x-lucide-image class="icon icon--sm" aria-hidden="true" />
            <span>{{ __('ui.photo_d84eebada9') }}</span>
        </a>
        <a href="{{ $href }}" class="quick-composer__tool">
            <x-lucide-video class="icon icon--sm" aria-hidden="true" />
            <span>{{ __('ui.video_d534be829e') }}</span>
        </a>
        <a href="{{ $href }}" class="quick-composer__tool">
            <x-lucide-circle-help class="icon icon--sm" aria-hidden="true" />
            <span>{{ __('ui.question_289aff12b0') }}</span>
        </a>
        @if ((int) $draftCount > 0)
            <a href="{{ route('home', ['feed' => 'drafts']) }}" class="quick-composer__drafts">
                {{ trans_choice('presentation.draft_count', (int) $draftCount, ['count' => $draftCount]) }}
            </a>
        @endif
    </div>
</section>
