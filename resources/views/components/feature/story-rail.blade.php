@props(['stories'])

<section class="story-rail" aria-labelledby="story-rail-title">
    <div class="story-rail__heading">
        <h2 id="story-rail-title">Stories</h2>
        <span>Fresh moments</span>
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
                        <span class="story__unseen" aria-label="New story"></span>
                    @endif
                </span>
                <span class="story__name">{{ $story['name'] }}</span>
                <span class="story__caption">{{ $story['caption'] }}</span>
            </a>
        @empty
            <p class="text-sm text-paw-muted">No active stories.</p>
        @endforelse
    </div>
</section>
