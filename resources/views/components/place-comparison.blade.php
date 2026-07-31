@props([
    'places',
])

@if (count($places) > 1)
    <section class="place-comparison" aria-labelledby="place-comparison-title">
        <header>
            <p>{{ __('ui.quick_comparison_e1d6250e16') }}</p>
            <h2 id="place-comparison-title">{{ __('ui.top_matches_ca85a90f5c') }}</h2>
        </header>
        <div class="place-comparison__grid" role="list">
            @forelse ($places as $place)
                <article class="place-comparison__card" role="listitem">
                    <h3><a href="{{ $place['detail_url'] }}">{{ $place['short_name'] }}</a></h3>
                    <dl>
                        <div>
                            <dt>{{ __('ui.travel_d2b98fb537') }}</dt>
                            <dd>{{ $place['travel_label'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.hours_21e8492938') }}</dt>
                            <dd>{{ $place['open_label'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.pet_rule_05863e283c') }}</dt>
                            <dd>{{ $place['leash_policy'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.access_ec5ba0abb7') }}</dt>
                            <dd>{{ $place['wheelchair_access'] ? __('ui.step_free_listed_7131755ea6') : __('ui.check_access_abb9a172a6') }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.source_0e570ca6fa') }}</dt>
                            <dd>{{ $place['verification']['label'] }}</dd>
                        </div>
                    </dl>
                </article>
            @empty
                <x-empty-state
                    icon="map-pin-off"
                    title="{{ __('ui.no_places_available_to_compare_ab63a16ec8') }}"
                />
            @endforelse
        </div>
    </section>
@endif
