@props(['href', 'draftCount' => 0])

<section class="quick-composer" aria-label="Create a publication">
    <div class="quick-composer__prompt">
        <x-avatar
            src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&crop=faces&w=96&h=96&q=80"
            alt="Mia Carter"
            size="header"
        />
        <a href="{{ $href }}" class="quick-composer__input">Share something useful with your circle...</a>
    </div>

    <div class="quick-composer__tools">
        <a href="{{ $href }}" class="quick-composer__tool">
            <x-lucide-image class="icon icon--sm" aria-hidden="true" />
            <span>Photo</span>
        </a>
        <a href="{{ $href }}" class="quick-composer__tool">
            <x-lucide-video class="icon icon--sm" aria-hidden="true" />
            <span>Video</span>
        </a>
        <a href="{{ $href }}" class="quick-composer__tool">
            <x-lucide-circle-help class="icon icon--sm" aria-hidden="true" />
            <span>Question</span>
        </a>
        @if ((int) $draftCount > 0)
            <a href="{{ route('home', ['feed' => 'drafts']) }}" class="quick-composer__drafts">
                {{ $draftCount }} {{ str('draft')->plural((int) $draftCount) }}
            </a>
        @endif
    </div>
</section>
