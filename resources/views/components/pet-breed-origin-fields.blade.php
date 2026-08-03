@props([
    'origin',
    'index',
    'classificationOptions' => [],
    'confidenceOptions' => [],
    'sourceOptions' => [],
    'showShare' => false,
])

<fieldset
    wire:key="managed-pet-breed-origin-{{ $origin['originKey'] }}"
    class="rounded-2xl border border-paw-line bg-white/70 p-4"
>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <legend class="font-semibold">
            {{ __('pet_profiles.breed_origin.entry_title', ['number' => $index + 1]) }}
        </legend>
        <button
            type="button"
            class="forum-button min-h-11"
            wire:click="removeBreedOrigin({{ $index }})"
            wire:loading.attr="disabled"
            wire:target="removeBreedOrigin({{ $index }})"
        >
            <x-ui-icon name="trash-2" />
            <span>{{ __('pet_profiles.breed_origin.remove_entry') }}</span>
        </button>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        @if ($classificationOptions !== [])
            <label class="forum-form__field" for="managed-pet-breed-classification-{{ $origin['originKey'] }}">
                <span>{{ __('pet_profiles.breed_origin.classification_label') }}</span>
                <select
                    id="managed-pet-breed-classification-{{ $origin['originKey'] }}"
                    wire:model="form.breedOrigins.{{ $index }}.classificationId"
                >
                    <option value="">{{ __('pet_profiles.breed_origin.classification_custom') }}</option>
                    @forelse ($classificationOptions as $classificationId => $classificationName)
                        <option wire:key="breed-classification-{{ $origin['originKey'] }}-{{ $classificationId }}" value="{{ $classificationId }}">{{ $classificationName }}</option>
                    @empty
                        <option value="">{{ __('pet_profiles.breed_origin.classification_custom') }}</option>
                    @endforelse
                </select>
                @error("form.breedOrigins.{$index}.classificationId") <small role="alert">{{ $message }}</small> @enderror
            </label>
        @endif

        <label class="forum-form__field" for="managed-pet-breed-name-{{ $origin['originKey'] }}">
            <span>{{ __('pet_profiles.breed_origin.name_label') }}</span>
            <input
                id="managed-pet-breed-name-{{ $origin['originKey'] }}"
                type="text"
                wire:model="form.breedOrigins.{{ $index }}.name"
                maxlength="220"
                aria-describedby="managed-pet-breed-name-help-{{ $origin['originKey'] }}"
            >
            <small id="managed-pet-breed-name-help-{{ $origin['originKey'] }}">{{ __('pet_profiles.breed_origin.name_help') }}</small>
            @error("form.breedOrigins.{$index}.name") <small role="alert">{{ $message }}</small> @enderror
        </label>

        <label class="forum-form__field" for="managed-pet-breed-confidence-{{ $origin['originKey'] }}">
            <span>{{ __('pet_profiles.breed_origin.confidence_label') }}</span>
            <select
                id="managed-pet-breed-confidence-{{ $origin['originKey'] }}"
                wire:model="form.breedOrigins.{{ $index }}.confidence"
            >
                @forelse ($confidenceOptions as $value => $label)
                    <option wire:key="breed-confidence-{{ $origin['originKey'] }}-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                @empty
                    <option value="owner-reported">{{ __('pet_profiles.breed_origin.confidences.owner-reported') }}</option>
                @endforelse
            </select>
            @error("form.breedOrigins.{$index}.confidence") <small role="alert">{{ $message }}</small> @enderror
        </label>

        <label class="forum-form__field" for="managed-pet-breed-source-{{ $origin['originKey'] }}">
            <span>{{ __('pet_profiles.breed_origin.source_label') }}</span>
            <select
                id="managed-pet-breed-source-{{ $origin['originKey'] }}"
                wire:model="form.breedOrigins.{{ $index }}.source"
            >
                @forelse ($sourceOptions as $value => $label)
                    <option wire:key="breed-source-{{ $origin['originKey'] }}-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                @empty
                    <option value="unknown">{{ __('pet_profiles.breed_origin.sources.unknown') }}</option>
                @endforelse
            </select>
            @error("form.breedOrigins.{$index}.source") <small role="alert">{{ $message }}</small> @enderror
        </label>

        @if ($showShare)
            <label class="forum-form__field" for="managed-pet-breed-share-{{ $origin['originKey'] }}">
                <span>{{ __('pet_profiles.breed_origin.share_label') }}</span>
                <input
                    id="managed-pet-breed-share-{{ $origin['originKey'] }}"
                    type="number"
                    wire:model="form.breedOrigins.{{ $index }}.approximateShare"
                    min="1"
                    max="100"
                    inputmode="numeric"
                    aria-describedby="managed-pet-breed-share-help-{{ $origin['originKey'] }}"
                >
                <small id="managed-pet-breed-share-help-{{ $origin['originKey'] }}">{{ __('pet_profiles.breed_origin.share_help') }}</small>
                @error("form.breedOrigins.{$index}.approximateShare") <small role="alert">{{ $message }}</small> @enderror
            </label>
        @endif
    </div>
</fieldset>
