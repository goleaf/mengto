<fieldset class="forum-form__field forum-form__field--full" aria-describedby="taxonomy-selector-help taxonomy-selector-status">
    <legend>{{ __('taxonomy.selector.label') }}</legend>
    <p id="taxonomy-selector-help" class="text-sm text-paw-muted">{{ __('taxonomy.selector.help') }}</p>

    @forelse ($this->selectedTaxa as $taxon)
        <input type="hidden" name="{{ $inputName }}" value="{{ $taxon['id'] }}">
        <div wire:key="selected-taxon-{{ $taxon['id'] }}" class="mt-2 flex min-h-11 items-center justify-between gap-3 rounded-md border border-paw-line bg-paw-paper px-3 py-2">
            <span class="min-w-0">
                <strong class="block truncate">{{ $taxon['name'] }}</strong>
                <small class="block text-paw-muted">
                    <i>{{ $taxon['scientific_name'] }}</i> · {{ $taxon['rank'] }}
                </small>
            </span>
            <button type="button" wire:click="removeTaxon({{ $taxon['id'] }})" class="forum-button min-h-11 min-w-11" aria-label="{{ __('taxonomy.selector.remove', ['name' => $taxon['name']]) }}">
                <x-lucide-x aria-hidden="true" />
            </button>
        </div>
    @empty
        <input type="hidden" name="animal_context" value="{{ $context }}">
    @endforelse

    <label class="mt-3 block">
        <span class="sr-only">{{ __('taxonomy.selector.search') }}</span>
        <input
            type="search"
            wire:model.live.debounce.350ms="search"
            placeholder="{{ __('taxonomy.selector.placeholder') }}"
            autocomplete="off"
            class="min-h-11 w-full rounded-md border border-paw-line bg-white px-3 py-2"
        >
    </label>

    <div wire:loading.delay wire:target="search" role="status" class="mt-2 text-sm text-paw-muted">
        {{ __('taxonomy.selector.searching') }}
    </div>

    @if (mb_strlen($search) >= 2)
        <div class="mt-2 max-h-72 overflow-y-auto rounded-md border border-paw-line bg-white" role="listbox" aria-label="{{ __('taxonomy.selector.results') }}">
            @forelse ($this->results as $result)
                <button
                    type="button"
                    wire:key="taxon-result-{{ $result['id'] }}"
                    wire:click="selectTaxon({{ $result['id'] }})"
                    class="flex min-h-11 w-full items-center justify-between gap-3 border-b border-paw-line px-3 py-2 text-start last:border-b-0 hover:bg-paw-mint"
                    role="option"
                >
                    <span>
                        <strong class="block">{{ $result['name'] }}</strong>
                        <small class="text-paw-muted"><i>{{ $result['scientific_name'] }}</i> · {{ $result['rank'] }}</small>
                    </span>
                    @if ($result['is_synonym'])
                        <span class="forum-badge">{{ __('taxonomy.selector.synonym') }}</span>
                    @endif
                </button>
            @empty
                <p class="p-3 text-sm text-paw-muted">{{ __('taxonomy.selector.no_results') }}</p>
            @endforelse
        </div>
    @endif

    <button type="button" wire:click="markUnidentified" class="forum-button mt-3 min-h-11">
        <x-lucide-circle-help aria-hidden="true" />
        {{ __('taxonomy.selector.unidentified') }}
    </button>

    @error('selectedTaxonIds')
        <p id="taxonomy-selector-status" role="alert" class="mt-2 text-sm text-status-danger">{{ $message }}</p>
    @else
        <p id="taxonomy-selector-status" aria-live="polite" class="sr-only">{{ __('taxonomy.selector.ready') }}</p>
    @enderror
</fieldset>
