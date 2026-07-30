@props(['days'])

<div class="care-weekly">
    <div class="care-weekly__bars" aria-hidden="true">
        @forelse ($days as $day)
            <div>
                <span style="--care-bar: {{ $day['activity_bar_percent'] }}%"></span>
                <small>{{ $day['date_short'] }}</small>
            </div>
        @empty
            <span>{{ __('ui.no_data_3b41ba9c7c') }}</span>
        @endforelse
    </div>
    <div class="care-table-scroll">
        <table class="care-table">
            <thead>
                <tr>
                    <th scope="col">{{ __('ui.date_99c40ab405') }}</th>
                    <th scope="col">{{ __('ui.feed_396c3cb18f') }}</th>
                    <th scope="col">{{ __('ui.water_7ca7dea906') }}</th>
                    <th scope="col">{{ __('ui.walk_08ee52ae12') }}</th>
                    <th scope="col">{{ __('ui.toilet_3e49ac277c') }}</th>
                    <th scope="col">{{ __('ui.sleep_d466bcf52e') }}</th>
                    <th scope="col">{{ __('ui.activity_38da1505ca') }}</th>
                    <th scope="col">{{ __('ui.notes_8a7525b149') }}</th>
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
                            <td>{{ $day['unusual'] ? __('presentation.unusual_count', ['count' => $day['unusual']]) : __('ui.none_marked_2d3a636956') }}</td>
                        @else
                            <td colspan="7">{{ __('ui.not_recorded_b37c7879f6') }}</td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="8">{{ __('ui.no_recorded_days_112f9e5f80') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
