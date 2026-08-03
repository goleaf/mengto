@props([
    'label',
    'steps',
])

<nav class="min-w-0 max-w-full" aria-label="{{ $label }}" data-pet-step-navigation>
    <ol class="grid min-w-0 max-w-full snap-x snap-mandatory grid-flow-col auto-cols-[85%] gap-3 overflow-x-auto pb-3 sm:grid-flow-row sm:auto-cols-auto sm:grid-cols-2 sm:overflow-visible sm:pb-0 sm:snap-none xl:grid-cols-3">
        @forelse ($steps as $step)
            <li class="snap-start" wire:key="pet-completion-step-{{ $step['value'] }}">
                <a
                    href="{{ $step['href'] }}"
                    @if ($step['active']) aria-current="step" @endif
                    @class([
                        'group flex min-h-24 h-full items-start gap-3 rounded-2xl border p-4 text-start transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-paw-accent',
                        'border-paw-accent bg-paw-accent/10 shadow-sm' => $step['active'],
                        'border-paw-line bg-paw-surface hover:border-paw-accent/60 hover:bg-paw-canvas' => ! $step['active'],
                    ])
                >
                    <span
                        @class([
                            'grid size-11 shrink-0 place-items-center rounded-xl border text-sm font-semibold',
                            'border-paw-accent bg-paw-accent text-white' => $step['active'],
                            'border-paw-line bg-paw-canvas text-paw-muted' => ! $step['active'],
                        ])
                        aria-hidden="true"
                    >
                        {{ $step['number'] }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-start justify-between gap-2">
                            <span class="font-semibold leading-5 text-paw-ink">{{ $step['label'] }}</span>
                            @if ($step['complete'])
                                <x-ui-icon name="circle-check" size="sm" class="mt-0.5 shrink-0 text-status-success" />
                            @endif
                        </span>
                        <span class="mt-1 block text-sm leading-5 text-paw-muted">{{ $step['description'] }}</span>
                        <span class="mt-2 block text-xs font-semibold uppercase tracking-wide text-paw-muted">{{ $step['state_label'] }}</span>
                    </span>
                </a>
            </li>
        @empty
            <li class="rounded-2xl border border-paw-line bg-paw-surface p-4 text-paw-muted">
                {{ __('pet_profiles.completion.empty') }}
            </li>
        @endforelse
    </ol>
</nav>
