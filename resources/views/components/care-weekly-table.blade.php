@props(['days'])

<div class="care-weekly">
    <div class="care-weekly__bars" aria-hidden="true">
        @forelse ($days as $day)
            <div>
                <span style="--care-bar: {{ min(100, $day['activity_minutes'] + $day['walk_minutes']) }}%"></span>
                <small>{{ str($day['date'])->before(',') }}</small>
            </div>
        @empty
            <span>No data</span>
        @endforelse
    </div>
    <div class="care-table-scroll">
        <table class="care-table">
            <thead>
                <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Feed</th>
                    <th scope="col">Water</th>
                    <th scope="col">Walk</th>
                    <th scope="col">Toilet</th>
                    <th scope="col">Sleep</th>
                    <th scope="col">Activity</th>
                    <th scope="col">Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($days as $day)
                    <tr>
                        <th scope="row">{{ $day['date'] }}</th>
                        @if ($day['recorded'])
                            <td>{{ $day['feeding'] }}</td>
                            <td>{{ $day['water'] }}</td>
                            <td>{{ $day['walk_minutes'] }} min</td>
                            <td>{{ $day['toilet'] }}</td>
                            <td>{{ $day['sleep_minutes'] }} min</td>
                            <td>{{ $day['activity_minutes'] }} min</td>
                            <td>{{ $day['unusual'] ? $day['unusual'].' unusual' : 'None marked' }}</td>
                        @else
                            <td colspan="7">Not recorded</td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="8">No recorded days.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
