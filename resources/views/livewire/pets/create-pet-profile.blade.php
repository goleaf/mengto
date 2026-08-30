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
                                width="1200"
                                height="900"
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
                        wire:model.live="form.species"
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
                    <label for="pet-profile-species-confidence">{{ __('pet_profiles.fields.species_confidence') }}</label>
                    <select
                        id="pet-profile-species-confidence"
                        class="field"
                        wire:model="form.speciesConfidence"
                        required
                        aria-describedby="pet-profile-species-confidence-help pet-profile-species-confidence-error"
                        @error('form.speciesConfidence') aria-invalid="true" @enderror
                    >
                        @forelse ($this->speciesConfidenceOptions as $value => $label)
                            <option wire:key="pet-species-confidence-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="unidentified">{{ __('pet_profiles.species_confidence.unidentified') }}</option>
                        @endforelse
                    </select>
                    <p id="pet-profile-species-confidence-help" class="pet-create__help">
                        {{ __('pet_profiles.create.species_confidence_help') }}
                    </p>
                    <p id="pet-profile-species-confidence-error" class="pet-create__error" aria-live="polite">
                        @error('form.speciesConfidence') {{ $message }} @enderror
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

            @if ($this->duplicateCandidates !== [])
                <section class="mt-6 grid gap-4 border-t border-paw-line pt-6" aria-labelledby="pet-duplicate-review-heading">
                    <header class="grid max-w-2xl gap-2">
                        <p class="pet-create__eyebrow">{{ __('pet_profiles.duplicate_review.eyebrow') }}</p>
                        <h3 id="pet-duplicate-review-heading" class="text-xl font-semibold">
                            {{ __('pet_profiles.duplicate_review.title') }}
                        </h3>
                        <p class="text-sm leading-6 text-paw-muted">
                            {{ __('pet_profiles.duplicate_review.description') }}
                        </p>
                    </header>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @forelse ($this->duplicateCandidates as $candidate)
                            <article class="grid min-w-0 gap-4 rounded-lg border border-paw-line bg-white p-4" wire:key="duplicate-candidate-{{ $candidate['profile_key'] }}">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-lg bg-paw-canvas">
                                        @if ($candidate['photo'] !== null)
                                            <img class="size-full object-cover" src="{{ $candidate['photo'] }}" alt="{{ $candidate['photo_alt'] }}" width="80" height="80" loading="lazy">
                                        @else
                                            <span role="img" aria-label="{{ $candidate['photo_alt'] }}"><x-ui-icon name="paw-print" size="lg" /></span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="break-words font-semibold">{{ $candidate['name'] }}</h4>
                                        <p class="text-sm text-paw-muted">{{ $candidate['species'] }}</p>
                                        @if ($candidate['age'] !== null)
                                            <p class="text-sm text-paw-muted">{{ $candidate['age'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="forum-button forum-button--primary min-h-11 w-full"
                                    wire:click="startAccessRequest('{{ $candidate['profile_key'] }}')"
                                >
                                    <x-ui-icon name="user-plus" />
                                    <span>{{ __('pet_profiles.duplicate_review.this_is_my_pet') }}</span>
                                </button>
                            </article>
                        @empty
                            <p>{{ __('pet_profiles.duplicate_review.empty') }}</p>
                        @endforelse
                    </div>

                    @if ($accessRequestFeedback !== '')
                        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
                            {{ $accessRequestFeedback }}
                        </p>
                    @endif

                    @if ($selectedDuplicateProfileKey !== '')
                        <fieldset class="forum-form grid gap-4">
                            <legend class="font-semibold">{{ __('pet_profiles.access_requests.form_title') }}</legend>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="forum-form__field" for="pet-access-request-type">
                                    <span>{{ __('pet_profiles.access_requests.request_type') }}</span>
                                    <select id="pet-access-request-type" wire:model.live="accessRequestForm.requestType">
                                        @forelse ($this->accessRequestTypes as $value => $label)
                                            <option wire:key="pet-access-type-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                                        @empty
                                            <option value="co-ownership">{{ __('pet_profiles.access_requests.types.co-ownership') }}</option>
                                        @endforelse
                                    </select>
                                </label>

                                @if ($accessRequestForm->requestType === 'relationship-correction')
                                    <label class="forum-form__field" for="pet-access-request-role">
                                        <span>{{ __('pet_profiles.access_requests.requested_role') }}</span>
                                        <select id="pet-access-request-role" wire:model="accessRequestForm.requestedRole">
                                            @forelse ($this->correctionRoleOptions as $value => $label)
                                                <option wire:key="pet-access-role-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                                            @empty
                                                <option value="family-member">{{ __('pet_profiles.manager_roles.family-member') }}</option>
                                            @endforelse
                                        </select>
                                    </label>
                                @endif

                                @if ($accessRequestForm->requestType === 'temporary-access')
                                    <label class="forum-form__field" for="pet-access-request-ends">
                                        <span>{{ __('pet_profiles.access_requests.temporary_ends_at') }}</span>
                                        <input id="pet-access-request-ends" type="datetime-local" wire:model="accessRequestForm.temporaryAccessEndsAt" required>
                                        @error('accessRequestForm.temporaryAccessEndsAt') <small role="alert">{{ $message }}</small> @enderror
                                    </label>
                                @endif

                                <label class="forum-form__field sm:col-span-2" for="pet-access-request-evidence">
                                    <span>{{ __('pet_profiles.access_requests.evidence') }}</span>
                                    <textarea id="pet-access-request-evidence" wire:model="accessRequestForm.evidenceSummary" rows="5" minlength="20" maxlength="2000" required aria-describedby="pet-access-request-evidence-help pet-access-request-evidence-error"></textarea>
                                    <small id="pet-access-request-evidence-help">{{ __('pet_profiles.access_requests.evidence_help') }}</small>
                                    @error('accessRequestForm.evidenceSummary') <small id="pet-access-request-evidence-error" role="alert">{{ $message }}</small> @enderror
                                </label>
                            </div>
                            @if ($accessRequestForm->requestType === 'ownership-transfer')
                                <x-notice icon="shield-alert" :title="__('pet_profiles.access_requests.protected_title')" :description="__('pet_profiles.access_requests.protected_description')" />
                            @endif
                            <div class="flex flex-wrap justify-end gap-2">
                                <button type="button" class="forum-button min-h-11" wire:click="cancelAccessRequest">
                                    {{ __('pet_profiles.actions.cancel') }}
                                </button>
                                <button type="button" class="forum-button forum-button--primary min-h-11" wire:click="submitSelectedAccessRequest" wire:loading.attr="disabled" wire:target="submitSelectedAccessRequest">
                                    <x-ui-icon name="send" />
                                    <span>{{ __('pet_profiles.access_requests.submit') }}</span>
                                </button>
                            </div>
                        </fieldset>
                    @endif
                </section>
            @endif

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
                @if ($this->duplicateCandidates !== [])
                    <button
                        type="button"
                        class="action action--primary action--regular"
                        wire:click="confirmDifferentAnimal"
                        wire:loading.attr="disabled"
                        wire:target="confirmDifferentAnimal"
                    >
                        <x-ui-icon name="plus" size="sm" />
                        <span>{{ __('pet_profiles.duplicate_review.different_animal') }}</span>
                    </button>
                @else
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
                @endif
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
