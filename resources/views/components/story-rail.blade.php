@props(['stories'])

<section class="story-rail" aria-labelledby="story-rail-title">
    <div class="story-rail__heading">
        <h2 id="story-rail-title">{{ __('ui.stories_6d09cf5748') }}</h2>
        <span>{{ __('ui.fresh_moments_acde105101') }}</span>
    </div>

    <div class="story-rail__items" role="list">
        @forelse ($stories as $story)
            <a href="{{ route($story['route']) }}" class="story" role="listitem">
                <span class="story__image-wrap">
                    <img
                        src="{{ $story['image'] }}"
                        alt=""
                        width="72"
                        height="72"
                        loading="lazy"
                        decoding="async"
                        class="story__image"
                    >
                    @if ($story['unseen'])
                        <span class="story__unseen" aria-label="{{ __('ui.new_story_df78ffebe2') }}"></span>
                    @endif
                </span>
                <span class="story__name">{{ $story['name'] }}</span>
                <span class="story__caption">{{ $story['caption'] }}</span>
            </a>
        @empty
            <p class="text-sm text-paw-muted">{{ __('ui.no_active_stories_db596948b2') }}</p>
        @endforelse
    </div>
</section>
