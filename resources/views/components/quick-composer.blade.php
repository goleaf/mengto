@props(['href', 'owner', 'draftCount' => 0])

<section data-current-user-composer class="quick-composer" aria-label="{{ __('ui.create_a_publication') }}">
    <div class="quick-composer__prompt">
        <span
            class="header-profile__initials"
            role="img"
            aria-label="{{ $owner['avatar_alt'] }}"
        >
            {{ $owner['initials'] }}
        </span>
        <a href="{{ $href }}" class="quick-composer__input">
            <x-ui-icon name="square-pen" size="sm" />
            <span>{{ __('ui.share_something_useful_with_your_circle') }}</span>
        </a>
    </div>

    <div class="quick-composer__tools">
        <a href="{{ $href }}" class="quick-composer__tool">
            <x-ui-icon name="image" size="sm" />
            <span>{{ __('ui.photo') }}</span>
        </a>
        <a href="{{ $href }}" class="quick-composer__tool">
            <x-ui-icon name="video" size="sm" />
            <span>{{ __('ui.video') }}</span>
        </a>
        <a href="{{ $href }}" class="quick-composer__tool">
            <x-ui-icon name="circle-help" size="sm" />
            <span>{{ __('ui.question') }}</span>
        </a>
        @if ((int) $draftCount > 0)
            <a href="{{ route('preview.feed', ['feed' => 'drafts']) }}" class="quick-composer__drafts">
                <x-ui-icon name="file-clock" size="sm" />
                <span>{{ trans_choice('presentation.draft_count', (int) $draftCount, ['count' => $draftCount]) }}</span>
            </a>
        @endif
    </div>
</section>
