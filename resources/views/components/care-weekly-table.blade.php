@props(['days'])

<div class="care-weekly">
    <div class="care-weekly__bars" aria-hidden="true">
        @forelse ($days as $day)
            <div>
                <span style="--care-bar: {{ $day['activity_bar_percent'] }}%"></span>
                <small>{{ $day['date_short'] }}</small>
            </div>
        @empty
            <span>{{ __('ui.no_data') }}</span>
        @endforelse
    </div>
    <div class="care-table-scroll">
        <table class="care-table">
            <thead>
                <tr>
                    <th scope="col">{{ __('ui.date') }}</th>
                    <th scope="col">{{ __('ui.feed') }}</th>
                    <th scope="col">{{ __('ui.water') }}</th>
                    <th scope="col">{{ __('ui.walk') }}</th>
                    <th scope="col">{{ __('ui.toilet') }}</th>
                    <th scope="col">{{ __('ui.sleep') }}</th>
                    <th scope="col">{{ __('ui.activity') }}</th>
                    <th scope="col">{{ __('ui.notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($days as $day)
                    <tr>
                        <th scope="row">{{ $day['date'] }}</th>
                        @if ($day['recorded'])
                            <td>{{ $day['feeding'] }}</td>
                            <td>{{ $day['water'] }}</td>
                            <td>{{ __('presentation.minutes', ['count' => $day['walk_minutes']]) }}</td>
                            <td>{{ $day['toilet'] }}</td>
                            <td>{{ __('presentation.minutes', ['count' => $day['sleep_minutes']]) }}</td>
                            <td>{{ __('presentation.minutes', ['count' => $day['activity_minutes']]) }}</td>
                            <td>{{ $day['unusual'] ? __('presentation.unusual_count', ['count' => $day['unusual']]) : __('ui.none_marked') }}</td>
                        @else
                            <td colspan="7">{{ __('ui.not_recorded') }}</td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="8">{{ __('ui.no_recorded_days') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
