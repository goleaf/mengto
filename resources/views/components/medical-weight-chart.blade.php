@props(['chart', 'weights'])

<section {{ $attributes->class(['medical-section']) }} aria-labelledby="weight-chart-title">
    <div class="medical-section__heading">
        <div>
            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.physical_measurements_4518dd1d39') }}</p>
            <h2 id="weight-chart-title" class="mt-1 text-xl font-bold">{{ __('ui.weight_trend_8044eea3f8') }}</h2>
        </div>
        <x-status-badge :label="$chart['trend']" icon="chart-no-axes-combined" tone="surface" />
    </div>

    @if ($chart['has_data'])
        <div class="medical-weight-chart" role="img" aria-label="{{ __('ui.weight_measurements_over_time_e7e83f2e62') }}">
            <svg viewBox="0 0 600 160" preserveAspectRatio="none" aria-hidden="true">
                <line x1="24" y1="28" x2="576" y2="28" />
                <line x1="24" y1="80" x2="576" y2="80" />
                <line x1="24" y1="132" x2="576" y2="132" />
                <polyline points="{{ $chart['path'] }}" />
                @forelse ($chart['points'] as $point)
                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4">
                        <title>{{ $point['date'] }} · {{ $point['label'] }}</title>
                    </circle>
                @empty
                @endforelse
            </svg>
            <div class="medical-weight-chart__range">
                <span>{{ $chart['minimum'] }}</span>
                <span>{{ $chart['maximum'] }}</span>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="medical-table min-w-[38rem]">
                <thead>
                    <tr>
                        <th>{{ __('ui.date_99c40ab405') }}</th>
                        <th>{{ __('ui.weight_81d27ef6d5') }}</th>
                        <th>{{ __('ui.source_0e570ca6fa') }}</th>
                        <th>{{ __('ui.context_a6e600a10f') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($weights as $weight)
                        <tr>
                            <td>{{ $weight['measured_at'] }}</td>
                            <td class="font-bold">{{ $weight['weight'] }}</td>
                            <td>{{ $weight['source_name'] }}<span>{{ $weight['verification'] }}</span></td>
                            <td>{{ $weight['context'] ?: __('ui.not_specified_dc12bec5d7') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">{{ __('ui.no_weight_entries_6daa2818fc') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="medical-empty">
            <x-ui-icon name="scale" size="xl" />
            <p>{{ __('ui.no_weight_measurements_yet_fc6e57877b') }}</p>
        </div>
    @endif
</section>
