<x-page-stack data-section="pet-profile-create">
    <x-page-header
        eyebrow="{{ __('pet_profiles.create.eyebrow') }}"
        title="{{ __('pet_profiles.create.title') }}"
        description="{{ __('pet_profiles.create.description') }}"
        heading-id="create-pet-profile-heading"
        action-label="{{ __('pet_profiles.actions.back_to_pets') }}"
        action-icon="arrow-left"
        :action-href="route('pets.index')"
    />

    <x-content-panel
        eyebrow="{{ __('pet_profiles.create.minimum_eyebrow') }}"
        title="{{ __('pet_profiles.create.minimum_title') }}"
    >
        <form wire:submit="create" class="mt-5 grid gap-5" novalidate>
            @if ($errors->any())
                <x-forum-error-summary
                    :messages="$errors->getMessages()"
                    :heading="__('pet_profiles.validation.summary')"
                />
            @endif

            <div class="grid min-w-0 gap-5 md:grid-cols-2">
                <label class="forum-form__field md:col-span-2" for="pet-profile-name">
                    <span>{{ __('pet_profiles.fields.name') }}</span>
                    <input
                        id="pet-profile-name"
                        type="text"
                        wire:model="form.name"
                        maxlength="120"
                        required
                        autocomplete="off"
                        aria-describedby="pet-profile-name-error"
                    >
                    @error('form.name')
                        <small id="pet-profile-name-error" role="alert">{{ $message }}</small>
                    @enderror
                </label>

                <label class="forum-form__field" for="pet-profile-species">
                    <span>{{ __('pet_profiles.fields.species') }}</span>
                    <select id="pet-profile-species" wire:model="form.species" required>
                        @forelse ($this->speciesOptions as $value => $label)
                            <option wire:key="pet-species-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="unknown">{{ __('pet_profiles.species.unknown') }}</option>
                        @endforelse
                    </select>
                </label>

                <label class="forum-form__field" for="pet-profile-breed">
                    <span>{{ __('pet_profiles.fields.breed') }}</span>
                    <input id="pet-profile-breed" type="text" wire:model="form.breed" maxlength="120">
                    @error('form.breed') <small role="alert">{{ $message }}</small> @enderror
                </label>
            </div>

            <livewire:forum.animal-taxonomy-selector
                wire:model.live="form.taxonIds"
                input-name="taxon_id"
                :selection-limit="1"
            />

            <div class="grid min-w-0 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <label class="forum-form__field" for="pet-profile-birth-date">
                    <span>{{ __('pet_profiles.fields.birth_date') }}</span>
                    <input id="pet-profile-birth-date" type="date" wire:model="form.birthDate" max="{{ now()->toDateString() }}">
                </label>
                <label class="forum-form__field" for="pet-profile-birth-precision">
                    <span>{{ __('pet_profiles.fields.birth_precision') }}</span>
                    <select id="pet-profile-birth-precision" wire:model="form.birthDatePrecision">
                        @forelse (['exact', 'estimated', 'month', 'year', 'age-estimate', 'unknown'] as $value)
                            <option wire:key="birth-precision-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.birth_precision.'.$value) }}</option>
                        @empty
                            <option value="unknown">{{ __('pet_profiles.birth_precision.unknown') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-profile-sex">
                    <span>{{ __('pet_profiles.fields.sex') }}</span>
                    <select id="pet-profile-sex" wire:model="form.sex">
                        @forelse (['male', 'female', 'unknown', 'undetermined', 'other-confirmed'] as $value)
                            <option wire:key="pet-sex-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.sex.'.$value) }}</option>
                        @empty
                            <option value="unknown">{{ __('pet_profiles.sex.unknown') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-profile-reproductive-status">
                    <span>{{ __('pet_profiles.fields.reproductive_status') }}</span>
                    <select id="pet-profile-reproductive-status" wire:model="form.reproductiveStatus">
                        @forelse (['intact', 'spayed', 'neutered', 'unknown', 'planned', 'medical-exception', 'not-applicable'] as $value)
                            <option wire:key="pet-reproductive-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.reproductive_status.'.$value) }}</option>
                        @empty
                            <option value="unknown">{{ __('pet_profiles.reproductive_status.unknown') }}</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <div class="grid min-w-0 gap-5 md:grid-cols-2">
                <label class="forum-form__field" for="pet-profile-relationship">
                    <span>{{ __('pet_profiles.fields.relationship') }}</span>
                    <select id="pet-profile-relationship" wire:model="form.relationshipRole" required>
                        @forelse ($this->relationshipOptions as $value => $label)
                            <option wire:key="pet-relationship-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="other">{{ __('pet_profiles.manager_roles.other') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-profile-visibility">
                    <span>{{ __('pet_profiles.fields.visibility') }}</span>
                    <select id="pet-profile-visibility" wire:model="form.visibility" required>
                        @forelse ($this->visibilityOptions as $value => $label)
                            <option wire:key="pet-visibility-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="private">{{ __('pet_profiles.visibility.private') }}</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <label class="forum-form__field" for="pet-profile-bio">
                <span>{{ __('pet_profiles.fields.bio') }}</span>
                <textarea id="pet-profile-bio" wire:model="form.bio" rows="5" maxlength="3000"></textarea>
                @error('form.bio') <small role="alert">{{ $message }}</small> @enderror
            </label>

            <p wire:dirty role="status" class="text-sm font-medium text-paw-muted">
                {{ __('pet_profiles.feedback.unsaved') }}
            </p>
            <p wire:offline role="status" class="text-sm font-medium text-paw-coral">
                {{ __('pet_profiles.feedback.offline') }}
            </p>

            <div>
                <button
                    type="submit"
                    class="forum-button forum-button--primary min-h-11"
                    wire:loading.attr="disabled"
                    wire:target="create"
                >
                    <x-lucide-plus aria-hidden="true" />
                    <span wire:loading.remove wire:target="create">{{ __('pet_profiles.actions.create') }}</span>
                    <span wire:loading wire:target="create">{{ __('pet_profiles.actions.creating') }}</span>
                </button>
            </div>
        </form>
    </x-content-panel>
</x-page-stack>
