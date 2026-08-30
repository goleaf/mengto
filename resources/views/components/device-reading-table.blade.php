@props(['readings', 'device' => null, 'shared' => false])

<div class="device-table-wrap">
    <table class="device-table">
        <thead>
            <tr>
                <th scope="col">{{ __('ui.reading') }}</th>
                <th scope="col">{{ __('ui.pet') }}</th>
                <th scope="col">{{ __('ui.recorded') }}</th>
                <th scope="col">{{ __('ui.quality') }}</th>
                @unless ($shared)
                    <th scope="col"><span class="sr-only">{{ __('ui.actions') }}</span></th>
                @endunless
            </tr>
        </thead>
        <tbody>
            @forelse ($readings as $reading)
                <tr>
                    <td data-label="{{ __('ui.reading') }}">
                        <strong>{{ $reading['value'] }}</strong>
                        <span>{{ $reading['metric_label'] }}</span>
                    </td>
                    <td data-label="{{ __('ui.pet') }}">{{ $reading['pet_name'] }}</td>
                    <td data-label="{{ __('ui.recorded') }}">
                        {{ $reading['recorded_at'] }}
                        @if ($reading['is_stale'])
                            <span class="device-table__warning">{{ __('ui.stale_data') }}</span>
                        @endif
                    </td>
                    <td data-label="{{ __('ui.quality') }}">
                        <span>{{ $reading['confidence'] }}</span>
                        <small>{{ $reading['verification'] }}{{ $reading['accuracy'] ? ' · '.$reading['accuracy'] : '' }}</small>
                    </td>
                    @unless ($shared)
                        <td data-label="{{ __('ui.action') }}">
                            @if ($reading['can_add_medical'])
                                <form method="POST" action="{{ route('devices.readings.medical-entry', [$device['slug'], $reading['id']]) }}">
                                    @csrf
                                    <input type="hidden" name="confirmed" value="1">
                                    <button class="device-text-button" type="submit">
                                        <x-ui-icon name="heart-pulse" size="sm" />
                                        <span>{{ __('ui.add_to_health') }}</span>
                                    </button>
                                </form>
                            @else
                                <span class="device-table__done">{{ __('ui.reviewed_or_unassigned') }}</span>
                            @endif
                        </td>
                    @endunless
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $shared ? 4 : 5 }}" class="device-table__empty">{{ __('ui.no_readings_are_available_for_this_access_scope') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
