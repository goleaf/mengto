<x-page-stack data-section="pet-profile-create">
    <x-page-header
        :eyebrow="__('pet_profiles.create.eyebrow')"
        :title="__('pet_profiles.create.title')"
        :description="__('pet_profiles.create.description')"
        heading-id="create-pet-profile-heading"
        :action-label="__('pet_profiles.actions.back_to_pets')"
        action-icon="arrow-left"
        :action-href="route('pets.index')"
    />

    <div class="pet-create">
        <form
            wire:submit="create"
            class="panel pet-create__form"
            aria-labelledby="pet-create-basics-heading"
            novalidate
        >
            <header class="pet-create__intro">
                <p class="pet-create__eyebrow">{{ __('pet_profiles.create.minimum_eyebrow') }}</p>
                <h2 id="pet-create-basics-heading" class="pet-create__title">
                    {{ __('pet_profiles.create.minimum_title') }}
                </h2>
                <p class="pet-create__description">{{ __('pet_profiles.create.minimum_description') }}</p>
            </header>

            @if ($errors->any())
                <x-forum-error-summary
                    :messages="$errors->getMessages()"
                    :heading="__('pet_profiles.validation.summary')"
                />
            @endif

            <div class="pet-create__fields">
                <div class="pet-create__field pet-create__field--wide">
                    <label for="pet-profile-name">{{ __('pet_profiles.fields.name') }}</label>
                    <input
                        id="pet-profile-name"
                        class="field"
                        type="text"
                        wire:model="form.name"
                        maxlength="120"
                        required
                        autocomplete="off"
                        aria-describedby="pet-profile-name-help pet-profile-name-error"
                        @error('form.name') aria-invalid="true" @enderror
                    >
                    <p id="pet-profile-name-help" class="pet-create__help">
                        {{ __('pet_profiles.create.name_help') }}
                    </p>
                    <p id="pet-profile-name-error" class="pet-create__error" aria-live="polite">
                        @error('form.name') {{ $message }} @enderror
                    </p>
                </div>

                <fieldset class="pet-create__photo pet-create__field--wide">
                    <legend>{{ __('pet_profiles.fields.primary_photo') }}</legend>

                    @if ($mediaForm->upload !== null)
                        <div class="pet-create__photo-preview">
                            <img
                                src="{{ $mediaForm->upload->temporaryUrl() }}"
                                alt="{{ __('pet_profiles.media.selected_preview') }}"
                            >
                            <button
                                type="button"
                                class="action action--paper action--regular"
                                wire:click="clearPhoto"
                            >
                                <x-ui-icon name="x" size="sm" />
                                {{ __('pet_profiles.actions.clear_photo') }}
                            </button>
                        </div>
                    @endif

                    <div class="pet-create__field">
                        <label for="pet-profile-photo">{{ __('pet_profiles.media.choose_photo') }}</label>
                        <input
                            id="pet-profile-photo"
                            class="field"
                            type="file"
                            wire:model="mediaForm.upload"
                            accept="image/jpeg,image/png,image/webp"
                            aria-describedby="pet-profile-photo-help pet-profile-photo-error"
                            @error('mediaForm.upload') aria-invalid="true" @enderror
                        >
                        <p id="pet-profile-photo-help" class="pet-create__help">
                            {{ __('pet_profiles.media.upload_help') }}
                        </p>
                        <p id="pet-profile-photo-error" class="pet-create__error" aria-live="polite">
                            @error('mediaForm.upload') {{ $message }} @enderror
                        </p>
                        <p class="pet-create__help" wire:loading wire:target="mediaForm.upload">
                            {{ __('pet_profiles.media.uploading') }}
                        </p>
                    </div>

                    <div class="pet-create__field">
                        <label for="pet-profile-photo-alt">{{ __('pet_profiles.fields.photo_alt_text') }}</label>
                        <input
                            id="pet-profile-photo-alt"
                            class="field"
                            type="text"
                            wire:model="mediaForm.altText"
                            maxlength="500"
                            aria-describedby="pet-profile-photo-alt-help pet-profile-photo-alt-error"
                            @error('mediaForm.altText') aria-invalid="true" @enderror
                        >
                        <p id="pet-profile-photo-alt-help" class="pet-create__help">
                            {{ __('pet_profiles.media.alt_help') }}
                        </p>
                        <p id="pet-profile-photo-alt-error" class="pet-create__error" aria-live="polite">
                            @error('mediaForm.altText') {{ $message }} @enderror
                        </p>
                    </div>
                </fieldset>

                <div class="pet-create__field">
                    <label for="pet-profile-species">{{ __('pet_profiles.fields.species') }}</label>
                    <select
                        id="pet-profile-species"
                        class="field"
                        wire:model="form.species"
                        required
                        aria-describedby="pet-profile-species-help pet-profile-species-error"
                        @error('form.species') aria-invalid="true" @enderror
                    >
                        @forelse ($this->speciesOptions as $value => $label)
                            <option wire:key="pet-species-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="unknown">{{ __('pet_profiles.species.unknown') }}</option>
                        @endforelse
                    </select>
                    <p id="pet-profile-species-help" class="pet-create__help">
                        {{ __('pet_profiles.create.species_help') }}
                    </p>
                    <p id="pet-profile-species-error" class="pet-create__error" aria-live="polite">
                        @error('form.species') {{ $message }} @enderror
                    </p>
                </div>

                <div class="pet-create__field">
                    <label for="pet-profile-relationship">{{ __('pet_profiles.fields.relationship') }}</label>
                    <select
                        id="pet-profile-relationship"
                        class="field"
                        wire:model="form.relationshipRole"
                        required
                        aria-describedby="pet-profile-relationship-help pet-profile-relationship-error"
                        @error('form.relationshipRole') aria-invalid="true" @enderror
                    >
                        @forelse ($this->relationshipOptions as $value => $label)
                            <option wire:key="pet-relationship-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="other">{{ __('pet_profiles.manager_roles.other') }}</option>
                        @endforelse
                    </select>
                    <p id="pet-profile-relationship-help" class="pet-create__help">
                        {{ __('pet_profiles.create.relationship_help') }}
                    </p>
                    <p id="pet-profile-relationship-error" class="pet-create__error" aria-live="polite">
                        @error('form.relationshipRole') {{ $message }} @enderror
                    </p>
                </div>

                <div class="pet-create__field pet-create__field--wide">
                    <label for="pet-profile-visibility">{{ __('pet_profiles.fields.visibility') }}</label>
                    <select
                        id="pet-profile-visibility"
                        class="field"
                        wire:model="form.visibility"
                        required
                        aria-describedby="pet-profile-visibility-help pet-profile-visibility-error"
                        @error('form.visibility') aria-invalid="true" @enderror
                    >
                        @forelse ($this->visibilityOptions as $value => $label)
                            <option wire:key="pet-visibility-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="private">{{ __('pet_profiles.visibility.private') }}</option>
                        @endforelse
                    </select>
                    <p id="pet-profile-visibility-help" class="pet-create__help">
                        {{ __('pet_profiles.create.visibility_help') }}
                    </p>
                    <p id="pet-profile-visibility-error" class="pet-create__error" aria-live="polite">
                        @error('form.visibility') {{ $message }} @enderror
                    </p>
                </div>
            </div>

            <div class="pet-create__privacy" role="note">
                <span class="pet-create__privacy-icon" aria-hidden="true">
                    <x-ui-icon name="lock-keyhole" />
                </span>
                <div>
                    <h3>{{ __('pet_profiles.create.privacy_title') }}</h3>
                    <p>{{ __('pet_profiles.create.privacy_description') }}</p>
                </div>
            </div>

            <div class="pet-create__status" aria-live="polite">
                <p wire:dirty>{{ __('pet_profiles.feedback.unsaved') }}</p>
                <p wire:offline class="pet-create__status--warning">{{ __('pet_profiles.feedback.offline') }}</p>
            </div>

            <footer class="pet-create__actions">
                <a href="{{ route('pets.index') }}" class="action action--paper action--regular">
                    <x-ui-icon name="x" size="sm" />
                    <span>{{ __('pet_profiles.actions.cancel') }}</span>
                </a>
                <button
                    type="submit"
                    class="action action--primary action--regular"
                    wire:loading.attr="disabled"
                    wire:target="create"
                >
                    <x-ui-icon name="plus" size="sm" />
                    <span wire:loading.remove wire:target="create">{{ __('pet_profiles.actions.create_draft') }}</span>
                    <span wire:loading wire:target="create">{{ __('pet_profiles.actions.creating') }}</span>
                </button>
            </footer>
        </form>

        <aside class="panel pet-create__guide" aria-labelledby="pet-create-guide-heading">
            <span class="pet-create__guide-mark" aria-hidden="true">
                <x-ui-icon name="paw-print" />
            </span>
            <p class="pet-create__eyebrow">{{ __('pet_profiles.create.guide_eyebrow') }}</p>
            <h2 id="pet-create-guide-heading" class="pet-create__guide-title">
                {{ __('pet_profiles.create.guide_title') }}
            </h2>
            <p class="pet-create__guide-description">{{ __('pet_profiles.create.guide_description') }}</p>

            <ol class="pet-create__steps">
                <li>
                    <span aria-hidden="true">01</span>
                    <p>{{ __('pet_profiles.create.guide_identity') }}</p>
                </li>
                <li>
                    <span aria-hidden="true">02</span>
                    <p>{{ __('pet_profiles.create.guide_details') }}</p>
                </li>
                <li>
                    <span aria-hidden="true">03</span>
                    <p>{{ __('pet_profiles.create.guide_publish') }}</p>
                </li>
            </ol>

            <div class="pet-create__boundary">
                <x-ui-icon name="shield-check" />
                <p>{{ __('pet_profiles.create.identity_note') }}</p>
            </div>
        </aside>
    </div>
</x-page-stack>
