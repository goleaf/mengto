@props(['readings', 'device' => null, 'shared' => false])

<div class="device-table-wrap">
    <table class="device-table">
        <thead>
            <tr>
                <th scope="col">Reading</th>
                <th scope="col">Pet</th>
                <th scope="col">Recorded</th>
                <th scope="col">Quality</th>
                @unless ($shared)
                    <th scope="col"><span class="sr-only">Actions</span></th>
                @endunless
            </tr>
        </thead>
        <tbody>
            @forelse ($readings as $reading)
                <tr>
                    <td data-label="Reading">
                        <strong>{{ $reading['value'] }}</strong>
                        <span>{{ $reading['metric_label'] }}</span>
                    </td>
                    <td data-label="Pet">{{ $reading['pet_name'] }}</td>
                    <td data-label="Recorded">
                        {{ $reading['recorded_at'] }}
                        @if ($reading['is_stale'])
                            <span class="device-table__warning">Stale data</span>
                        @endif
                    </td>
                    <td data-label="Quality">
                        <span>{{ $reading['confidence'] }}</span>
                        <small>{{ $reading['verification'] }}{{ $reading['accuracy'] ? ' · '.$reading['accuracy'] : '' }}</small>
                    </td>
                    @unless ($shared)
                        <td data-label="Action">
                            @if ($reading['can_add_medical'])
                                <form method="POST" action="{{ route('devices.readings.medical-entry', [$device['slug'], $reading['id']]) }}">
                                    @csrf
                                    <input type="hidden" name="confirmed" value="1">
                                    <button class="device-text-button" type="submit">Add to health</button>
                                </form>
                            @else
                                <span class="device-table__done">Reviewed or unassigned</span>
                            @endif
                        </td>
                    @endunless
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $shared ? 4 : 5 }}" class="device-table__empty">No readings are available for this access scope.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
