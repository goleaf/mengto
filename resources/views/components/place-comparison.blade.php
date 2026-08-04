@props([
    'places',
])

@if (count($places) > 1)
    <section class="place-comparison" aria-labelledby="place-comparison-title">
        <header>
            <p>{{ __('place_directory.comparison.eyebrow') }}</p>
            <h2 id="place-comparison-title">{{ __('place_directory.comparison.title') }}</h2>
        </header>
        <div class="place-comparison__grid" role="list">
            @forelse ($places as $place)
                <article class="place-comparison__card" role="listitem">
                    <h3><a href="{{ $place['detail_url'] }}">{{ $place['short_name'] }}</a></h3>
                    <dl>
                        <div>
                            <dt>{{ __('place_directory.comparison.travel') }}</dt>
                            <dd>{{ $place['travel_label'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('place_directory.comparison.hours') }}</dt>
                            <dd>{{ $place['open_label'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('place_directory.comparison.pet_rule') }}</dt>
                            <dd>{{ $place['leash_policy'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('place_directory.comparison.access') }}</dt>
                            <dd>{{ $place['wheelchair_access'] ? __('place_directory.comparison.step_free') : __('place_directory.comparison.check_access') }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('place_directory.comparison.source') }}</dt>
                            <dd>{{ $place['verification']['label'] }}</dd>
                        </div>
                    </dl>
                </article>
            @empty
                <x-empty-state
                    icon="map-pin-off"
                    title="{{ __('place_directory.comparison.empty') }}"
                />
            @endforelse
        </div>
    </section>
@endif
