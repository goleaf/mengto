<x-page-stack data-section="pet-profile-management">
    <x-page-header
        :eyebrow="__('pet_profiles.manage.eyebrow')"
        :title="__('pet_profiles.manage.title', ['name' => $profile->name])"
        :description="__('pet_profiles.manage.description')"
        heading-id="manage-pet-profile-heading"
        :action-label="__('pet_profiles.actions.view_profile')"
        action-icon="external-link"
        :action-href="$profileUrl"
    />

    @if ($feedback !== '')
        <p class="rounded-xl border border-status-success/40 bg-status-success/10 px-4 py-3" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <section class="min-w-0 rounded-3xl border border-paw-line bg-paw-surface p-4 shadow-sm sm:p-6" aria-labelledby="pet-completion-heading">
        <div class="mb-5 max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-paw-accent">{{ __('pet_profiles.completion.eyebrow') }}</p>
            <h2 id="pet-completion-heading" class="mt-2 text-2xl font-semibold text-paw-ink">{{ __('pet_profiles.completion.title') }}</h2>
            <p class="mt-2 leading-7 text-paw-muted">{{ __('pet_profiles.completion.description') }}</p>
            <p class="mt-2 text-sm leading-6 text-paw-muted">{{ __('pet_profiles.completion.no_score') }}</p>
        </div>

        <x-pet-profile-step-navigation
            :label="__('pet_profiles.manage.sections')"
            :steps="$this->completionSteps"
        />
    </section>

    <section
        id="pet-step-{{ $activeStep['value'] }}"
        aria-labelledby="pet-step-heading"
        class="min-w-0 rounded-3xl border border-paw-line bg-paw-surface p-4 shadow-sm sm:p-6"
    >
        <header class="border-b border-paw-line pb-5">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-paw-accent">
                {{ __('pet_profiles.completion.step_count', ['current' => $activeStep['number'], 'total' => 12]) }}
            </p>
            <h2 id="pet-step-heading" class="mt-2 text-2xl font-semibold text-paw-ink">{{ $activeStep['label'] }}</h2>
            <p class="mt-2 max-w-3xl leading-7 text-paw-muted">{{ $activeStep['description'] }}</p>
            <div class="mt-4 flex gap-3 rounded-2xl border border-paw-line bg-paw-canvas p-4">
                <x-ui-icon name="circle-help" class="mt-0.5 shrink-0 text-paw-accent" />
                <p class="text-sm leading-6 text-paw-muted"><strong class="text-paw-ink">{{ __('pet_profiles.completion.why_label') }}</strong> {{ $activeStep['why'] }}</p>
            </div>
            @if ($activeStep['next_href'] !== null)
                <a class="forum-button mt-4 min-h-11" href="{{ $activeStep['next_href'] }}">
                    {{ __('pet_profiles.actions.skip_for_now') }}
                    <x-ui-icon name="arrow-right" />
                </a>
            @endif
        </header>

        @if ($activeStep['value'] === 'basics')
            <form wire:submit="saveBasics" wire:change="autoSaveStep('basics', $event.currentTarget.dataset.petProfileAutosaveRevision)" class="forum-form mt-6" data-pet-profile-autosave-step="basics">
                @if ($errors->any())
                    <x-forum-error-summary :messages="$errors->getMessages()" :heading="__('pet_profiles.validation.summary')" />
                @endif
                <div class="grid min-w-0 gap-4 md:grid-cols-2">
                    <label class="forum-form__field" for="managed-pet-name">
                        <span>{{ __('pet_profiles.fields.name') }}</span>
                        <input id="managed-pet-name" type="text" wire:model="form.name" maxlength="120" required aria-describedby="managed-pet-name-help managed-pet-name-error" @error('form.name') aria-invalid="true" @enderror>
                        <small id="managed-pet-name-help">{{ __('pet_profiles.completion.help.name') }}</small>
                        @error('form.name') <small id="managed-pet-name-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field" for="managed-pet-species">
                        <span>{{ __('pet_profiles.fields.species') }}</span>
                        <select id="managed-pet-species" wire:model="form.species" aria-describedby="managed-pet-species-help managed-pet-species-error" @error('form.species') aria-invalid="true" @enderror>
                            @forelse ($this->speciesOptions as $value => $label)
                                <option wire:key="managed-pet-species-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option value="unknown">{{ __('pet_profiles.species.unknown') }}</option>
                            @endforelse
                        </select>
                        <small id="managed-pet-species-help">{{ __('pet_profiles.completion.help.species') }}</small>
                        @error('form.species') <small id="managed-pet-species-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field" for="managed-pet-species-confidence">
                        <span>{{ __('pet_profiles.fields.species_confidence') }}</span>
                        <select id="managed-pet-species-confidence" wire:model="form.speciesConfidence" aria-describedby="managed-pet-species-confidence-help managed-pet-species-confidence-error" @error('form.speciesConfidence') aria-invalid="true" @enderror>
                            @forelse ($this->speciesConfidenceOptions as $value => $label)
                                <option wire:key="managed-pet-species-confidence-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option value="unidentified">{{ __('pet_profiles.species_confidence.unidentified') }}</option>
                            @endforelse
                        </select>
                        <small id="managed-pet-species-confidence-help">{{ __('pet_profiles.completion.help.species_confidence') }}</small>
                        @error('form.speciesConfidence') <small id="managed-pet-species-confidence-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                </div>
                <x-pet-profile-save-status :feedback="$feedback" />
                <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="saveBasics">
                    <x-ui-icon name="save" />
                    <span wire:loading.remove wire:target="saveBasics">{{ __('pet_profiles.actions.save_step') }}</span>
                    <span wire:loading wire:target="saveBasics">{{ __('pet_profiles.actions.saving') }}</span>
                </button>
            </form>

            <section class="mt-8 border-t border-paw-line pt-6" aria-labelledby="pet-alternative-names-heading">
                <div class="max-w-3xl">
                    <h3 id="pet-alternative-names-heading" class="text-xl font-semibold text-paw-ink">{{ __('pet_profiles.names.title') }}</h3>
                    <p class="mt-2 leading-7 text-paw-muted">{{ __('pet_profiles.names.description') }}</p>
                </div>

                <form wire:submit="addAlternativeName" class="forum-form mt-5">
                    <div class="grid min-w-0 gap-4 md:grid-cols-2">
                        <label class="forum-form__field" for="managed-pet-alternative-name">
                            <span>{{ __('pet_profiles.fields.alternative_name') }}</span>
                            <input id="managed-pet-alternative-name" type="text" wire:model="nameForm.name" maxlength="120" required aria-describedby="managed-pet-alternative-name-help managed-pet-alternative-name-error" @error('nameForm.name') aria-invalid="true" @enderror>
                            <small id="managed-pet-alternative-name-help">{{ __('pet_profiles.names.name_help') }}</small>
                            @error('nameForm.name') <small id="managed-pet-alternative-name-error" role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__field" for="managed-pet-name-type">
                            <span>{{ __('pet_profiles.fields.name_type') }}</span>
                            <select id="managed-pet-name-type" wire:model.live="nameForm.type" aria-describedby="managed-pet-name-type-help managed-pet-name-type-error" @error('nameForm.type') aria-invalid="true" @enderror>
                                @forelse ($this->nameTypeOptions as $value => $label)
                                    <option wire:key="managed-pet-name-type-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                                @empty
                                    <option value="nickname">{{ __('pet_profiles.name_types.nickname') }}</option>
                                @endforelse
                            </select>
                            <small id="managed-pet-name-type-help">{{ __('pet_profiles.names.type_help') }}</small>
                            @error('nameForm.type') <small id="managed-pet-name-type-error" role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__field" for="managed-pet-name-visibility">
                            <span>{{ __('pet_profiles.fields.name_visibility') }}</span>
                            <select id="managed-pet-name-visibility" wire:model="nameForm.visibility" aria-describedby="managed-pet-name-visibility-help managed-pet-name-visibility-error" @error('nameForm.visibility') aria-invalid="true" @enderror>
                                @forelse ($this->nameVisibilityOptions as $value => $label)
                                    <option wire:key="managed-pet-name-visibility-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                                @empty
                                    <option value="private">{{ __('pet_profiles.name_visibility.private') }}</option>
                                @endforelse
                            </select>
                            <small id="managed-pet-name-visibility-help">{{ __('pet_profiles.names.visibility_help') }}</small>
                            @error('nameForm.visibility') <small id="managed-pet-name-visibility-error" role="alert">{{ $message }}</small> @enderror
                        </label>
                        @if ($nameForm->type === 'localized')
                            <label class="forum-form__field" for="managed-pet-name-locale">
                                <span>{{ __('pet_profiles.fields.name_locale') }}</span>
                                <input id="managed-pet-name-locale" type="text" wire:model="nameForm.locale" maxlength="16" placeholder="{{ __('pet_profiles.names.locale_placeholder') }}" required aria-describedby="managed-pet-name-locale-help managed-pet-name-locale-error" @error('nameForm.locale') aria-invalid="true" @enderror>
                                <small id="managed-pet-name-locale-help">{{ __('pet_profiles.names.locale_help') }}</small>
                                @error('nameForm.locale') <small id="managed-pet-name-locale-error" role="alert">{{ $message }}</small> @enderror
                            </label>
                        @endif
                    </div>
                    <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="addAlternativeName">
                        <x-ui-icon name="plus" />
                        <span wire:loading.remove wire:target="addAlternativeName">{{ __('pet_profiles.actions.add_name') }}</span>
                        <span wire:loading wire:target="addAlternativeName">{{ __('pet_profiles.actions.saving') }}</span>
                    </button>
                </form>

                <div class="mt-5 grid gap-3" aria-live="polite">
                    @forelse ($this->alternativeNames as $alternativeName)
                        <article wire:key="managed-pet-name-{{ $alternativeName['id'] }}" class="flex min-w-0 flex-col gap-3 rounded-2xl border border-paw-line bg-paw-canvas p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="break-words font-semibold text-paw-ink" @if ($alternativeName['locale'] !== null) lang="{{ $alternativeName['locale'] }}" @endif>{{ $alternativeName['name'] }}</p>
                                <p class="mt-1 text-sm text-paw-muted">{{ $alternativeName['type'] }} · {{ $alternativeName['visibility'] }}</p>
                            </div>
                            <button type="button" class="forum-button min-h-11 shrink-0" wire:click="removeAlternativeName({{ $alternativeName['id'] }})" wire:confirm="{{ __('pet_profiles.confirmations.remove_name') }}">
                                <x-ui-icon name="trash-2" />
                                {{ __('pet_profiles.actions.remove_name') }}
                            </button>
                        </article>
                    @empty
                        <p class="rounded-2xl border border-dashed border-paw-line p-4 text-paw-muted">{{ __('pet_profiles.names.empty') }}</p>
                    @endforelse
                </div>
            </section>
        @elseif ($activeStep['value'] === 'photos')
            <div class="mt-6 grid gap-5 lg:grid-cols-[14rem_minmax(0,1fr)] lg:items-start">
                <div class="aspect-square overflow-hidden rounded-2xl border border-paw-line bg-paw-canvas">
                    @if ($mediaForm->upload !== null)
                        <img src="{{ $mediaForm->upload->temporaryUrl() }}" alt="{{ __('pet_profiles.media.selected_preview') }}" class="h-full w-full object-cover">
                    @elseif ($primaryPhoto !== null)
                        <img src="{{ $primaryPhoto['url'] }}" alt="{{ $primaryPhoto['alt_text'] }}" class="h-full w-full object-cover">
                    @else
                        <div class="grid h-full place-items-center" role="img" aria-label="{{ __('pet_profiles.public.avatar_alt', ['name' => $profile->name]) }}">
                            <x-ui-icon name="paw-print" size="3xl" />
                        </div>
                    @endif
                </div>
                <div class="grid gap-4">
                    <form wire:submit="replacePrimaryPhoto" class="forum-form">
                        <label class="forum-form__field" for="managed-pet-photo">
                            <span>{{ $primaryPhoto === null ? __('pet_profiles.media.choose_photo') : __('pet_profiles.media.choose_replacement') }}</span>
                            <input id="managed-pet-photo" type="file" wire:model="mediaForm.upload" accept="image/jpeg,image/png,image/webp" aria-describedby="managed-pet-photo-help managed-pet-photo-error" @error('mediaForm.upload') aria-invalid="true" @enderror>
                            <small id="managed-pet-photo-help">{{ __('pet_profiles.media.upload_help') }}</small>
                            @error('mediaForm.upload') <small id="managed-pet-photo-error" role="alert">{{ $message }}</small> @enderror
                            <small wire:loading wire:target="mediaForm.upload">{{ __('pet_profiles.media.uploading') }}</small>
                        </label>
                        <label class="forum-form__field" for="managed-pet-photo-alt">
                            <span>{{ __('pet_profiles.fields.photo_alt_text') }}</span>
                            <input id="managed-pet-photo-alt" type="text" wire:model="mediaForm.altText" maxlength="500" aria-describedby="managed-pet-photo-alt-help managed-pet-photo-alt-error" @error('mediaForm.altText') aria-invalid="true" @enderror>
                            <small id="managed-pet-photo-alt-help">{{ __('pet_profiles.media.alt_help') }}</small>
                            @error('mediaForm.altText') <small id="managed-pet-photo-alt-error" role="alert">{{ $message }}</small> @enderror
                        </label>
                        @if ($mediaForm->upload !== null)
                            <p class="text-sm text-paw-muted" role="status" aria-live="polite">
                                {{ __('pet_profiles.feedback.unsaved') }}
                            </p>
                        @endif
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="replacePrimaryPhoto,mediaForm.upload">
                                <x-ui-icon name="image-plus" />
                                <span wire:loading.remove wire:target="replacePrimaryPhoto">{{ __('pet_profiles.actions.save_photo') }}</span>
                                <span wire:loading wire:target="replacePrimaryPhoto">{{ __('pet_profiles.actions.saving') }}</span>
                            </button>
                            @if ($mediaForm->upload !== null)
                                <button type="button" class="forum-button min-h-11" wire:click="clearPhoto"><x-ui-icon name="x" />{{ __('pet_profiles.actions.clear_photo') }}</button>
                            @endif
                            @if ($primaryPhoto !== null)
                                <button type="button" class="forum-button min-h-11" wire:click="removePrimaryPhoto" wire:confirm="{{ __('pet_profiles.confirmations.remove_photo') }}"><x-ui-icon name="trash-2" />{{ __('pet_profiles.actions.remove_photo') }}</button>
                            @endif
                        </div>
                    </form>
                    @if ($recoverablePhoto !== null)
                        <div class="rounded-2xl border border-paw-line bg-paw-canvas p-4">
                            <h3>{{ __('pet_profiles.media.recovery_title') }}</h3>
                            <p class="mt-2 text-sm leading-6 text-paw-muted">{{ __('pet_profiles.media.recovery_description') }}</p>
                            <button type="button" class="forum-button mt-3 min-h-11" wire:click="restorePrimaryPhoto('{{ $recoverablePhoto['media_key'] }}')"><x-ui-icon name="rotate-ccw" />{{ __('pet_profiles.actions.restore_photo') }}</button>
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($activeStep['value'] === 'age-sex')
            <form wire:submit="saveAgeAndSex" wire:change="autoSaveStep('age-sex', $event.currentTarget.dataset.petProfileAutosaveRevision)" class="forum-form mt-6" data-pet-profile-autosave-step="age-sex">
                @if ($errors->any())
                    <x-forum-error-summary :messages="$errors->getMessages()" :heading="__('pet_profiles.validation.summary')" />
                @endif
                <div class="grid min-w-0 gap-4 md:grid-cols-2">
                    <label class="forum-form__field md:col-span-2" for="managed-pet-birth-precision">
                        <span>{{ __('pet_profiles.fields.birth_precision') }}</span>
                        <select id="managed-pet-birth-precision" wire:model.live="form.birthDatePrecision" x-on:change.stop aria-describedby="managed-pet-birth-precision-help managed-pet-birth-precision-error" @error('form.birthDatePrecision') aria-invalid="true" @enderror>
                            @forelse ($this->birthPrecisionOptions as $value => $label)
                                <option wire:key="managed-birth-precision-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option value="unknown">{{ __('pet_profiles.birth_precision.unknown') }}</option>
                            @endforelse
                        </select>
                        <small id="managed-pet-birth-precision-help">{{ __('pet_profiles.completion.help.birth_precision') }}</small>
                        @error('form.birthDatePrecision') <small id="managed-pet-birth-precision-error" role="alert">{{ $message }}</small> @enderror
                    </label>

                    @if ($form->birthDatePrecision === 'exact' || $form->birthDatePrecision === 'estimated')
                        <label class="forum-form__field md:col-span-2" for="managed-pet-birth-date">
                            <span>{{ $form->birthDatePrecision === 'exact' ? __('pet_profiles.fields.birth_date') : __('pet_profiles.fields.estimated_birth_date') }}</span>
                            <input id="managed-pet-birth-date" type="date" wire:model="form.birthDate" max="{{ $today }}" required aria-describedby="managed-pet-birth-date-help managed-pet-birth-date-error" @error('form.birthDate') aria-invalid="true" @enderror>
                            <small id="managed-pet-birth-date-help">{{ __('pet_profiles.completion.help.birth_date') }}</small>
                            @error('form.birthDate') <small id="managed-pet-birth-date-error" role="alert">{{ $message }}</small> @enderror
                        </label>
                    @elseif ($form->birthDatePrecision === 'month')
                        <label class="forum-form__field md:col-span-2" for="managed-pet-birth-month">
                            <span>{{ __('pet_profiles.fields.birth_month') }}</span>
                            <input id="managed-pet-birth-month" type="month" wire:model="form.birthMonth" max="{{ $currentMonth }}" required aria-describedby="managed-pet-birth-month-help managed-pet-birth-month-error" @error('form.birthMonth') aria-invalid="true" @enderror>
                            <small id="managed-pet-birth-month-help">{{ __('pet_profiles.completion.help.birth_month') }}</small>
                            @error('form.birthMonth') <small id="managed-pet-birth-month-error" role="alert">{{ $message }}</small> @enderror
                        </label>
                    @elseif ($form->birthDatePrecision === 'year')
                        <label class="forum-form__field md:col-span-2" for="managed-pet-birth-year">
                            <span>{{ __('pet_profiles.fields.birth_year') }}</span>
                            <input id="managed-pet-birth-year" type="number" wire:model="form.birthYear" min="{{ $minimumBirthYear }}" max="{{ $currentYear }}" inputmode="numeric" required aria-describedby="managed-pet-birth-year-help managed-pet-birth-year-error" @error('form.birthYear') aria-invalid="true" @enderror>
                            <small id="managed-pet-birth-year-help">{{ __('pet_profiles.completion.help.birth_year') }}</small>
                            @error('form.birthYear') <small id="managed-pet-birth-year-error" role="alert">{{ $message }}</small> @enderror
                        </label>
                    @elseif ($form->birthDatePrecision === 'age-estimate')
                        <label class="forum-form__field" for="managed-pet-estimated-age-years">
                            <span>{{ __('pet_profiles.fields.estimated_age_years') }}</span>
                            <input id="managed-pet-estimated-age-years" type="number" wire:model="form.estimatedAgeYears" min="0" max="{{ $maximumAgeYears }}" inputmode="numeric" required aria-describedby="managed-pet-estimated-age-help managed-pet-estimated-age-years-error" @error('form.estimatedAgeYears') aria-invalid="true" @enderror>
                            <small id="managed-pet-estimated-age-help">{{ __('pet_profiles.completion.help.estimated_age') }}</small>
                            @error('form.estimatedAgeYears') <small id="managed-pet-estimated-age-years-error" role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__field" for="managed-pet-estimated-age-months">
                            <span>{{ __('pet_profiles.fields.estimated_age_months') }}</span>
                            <input id="managed-pet-estimated-age-months" type="number" wire:model="form.estimatedAgeMonths" min="0" max="11" inputmode="numeric" required aria-describedby="managed-pet-estimated-age-help managed-pet-estimated-age-months-error" @error('form.estimatedAgeMonths') aria-invalid="true" @enderror>
                            @error('form.estimatedAgeMonths') <small id="managed-pet-estimated-age-months-error" role="alert">{{ $message }}</small> @enderror
                        </label>
                    @else
                        <p class="md:col-span-2 rounded-2xl border border-paw-line bg-paw-canvas p-4 text-sm leading-6 text-paw-muted">
                            {{ __('pet_profiles.completion.help.age_unknown') }}
                        </p>
                    @endif

                    @if ($form->birthDatePrecision !== 'exact')
                        <fieldset class="md:col-span-2 grid min-w-0 gap-4 rounded-2xl border border-paw-line p-4 md:grid-cols-2">
                            <legend class="px-2 font-semibold">{{ __('pet_profiles.fields.celebration_day') }}</legend>
                            <p class="md:col-span-2 text-sm leading-6 text-paw-muted">{{ __('pet_profiles.completion.help.celebration_day') }}</p>
                            <label class="forum-form__field" for="managed-pet-celebration-month">
                                <span>{{ __('pet_profiles.fields.celebration_month') }}</span>
                                <input id="managed-pet-celebration-month" type="number" wire:model="form.celebrationMonth" x-on:change.stop min="1" max="12" inputmode="numeric" aria-describedby="managed-pet-celebration-month-error" @error('form.celebrationMonth') aria-invalid="true" @enderror>
                                @error('form.celebrationMonth') <small id="managed-pet-celebration-month-error" role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field" for="managed-pet-celebration-day">
                                <span>{{ __('pet_profiles.fields.celebration_day_of_month') }}</span>
                                <input id="managed-pet-celebration-day" type="number" wire:model="form.celebrationDay" x-on:change.stop min="1" max="31" inputmode="numeric" aria-describedby="managed-pet-celebration-day-error" @error('form.celebrationDay') aria-invalid="true" @enderror>
                                @error('form.celebrationDay') <small id="managed-pet-celebration-day-error" role="alert">{{ $message }}</small> @enderror
                            </label>
                        </fieldset>
                    @endif

                    @if ($currentAgeLabel !== null)
                        <p class="md:col-span-2 text-sm text-paw-muted" role="status">
                            {{ __('pet_profiles.completion.current_age', ['age' => $currentAgeLabel]) }}
                        </p>
                    @endif
                    <label class="forum-form__field" for="managed-pet-sex">
                        <span>{{ __('pet_profiles.fields.sex') }}</span>
                        <select id="managed-pet-sex" wire:model="form.sex">
                            @forelse (['male', 'female', 'unknown', 'undetermined', 'other-confirmed'] as $value)
                                <option wire:key="managed-pet-sex-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.sex.'.$value) }}</option>
                            @empty
                                <option value="unknown">{{ __('pet_profiles.sex.unknown') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="forum-form__field" for="managed-pet-reproductive-status">
                        <span>{{ __('pet_profiles.fields.reproductive_status') }}</span>
                        <select id="managed-pet-reproductive-status" wire:model="form.reproductiveStatus">
                            @forelse (['intact', 'spayed', 'neutered', 'unknown', 'planned', 'medical-exception', 'not-applicable'] as $value)
                                <option wire:key="managed-pet-reproductive-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.reproductive_status.'.$value) }}</option>
                            @empty
                                <option value="unknown">{{ __('pet_profiles.reproductive_status.unknown') }}</option>
                            @endforelse
                        </select>
                    </label>
                </div>
                <x-pet-profile-save-status :feedback="$feedback" />
                <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="saveAgeAndSex"><x-ui-icon name="save" /><span wire:loading.remove wire:target="saveAgeAndSex">{{ __('pet_profiles.actions.save_step') }}</span><span wire:loading wire:target="saveAgeAndSex">{{ __('pet_profiles.actions.saving') }}</span></button>
            </form>
        @elseif ($activeStep['value'] === 'breed-origin')
            <form wire:submit="saveBreedAndOrigin" wire:change="autoSaveStep('breed-origin', $event.currentTarget.dataset.petProfileAutosaveRevision)" class="forum-form mt-6" data-pet-profile-autosave-step="breed-origin">
                <label class="forum-form__field" for="managed-pet-breed">
                    <span>{{ __('pet_profiles.fields.breed') }}</span>
                    <input id="managed-pet-breed" type="text" wire:model="form.breed" maxlength="120" aria-describedby="managed-pet-breed-help managed-pet-breed-error" @error('form.breed') aria-invalid="true" @enderror>
                    <small id="managed-pet-breed-help">{{ __('pet_profiles.completion.help.breed') }}</small>
                    @error('form.breed') <small id="managed-pet-breed-error" role="alert">{{ $message }}</small> @enderror
                </label>
                <livewire:forum.animal-taxonomy-selector wire:model.live="form.taxonIds" input-name="taxon_id" :selection-limit="1" />
                <x-pet-profile-save-status :feedback="$feedback" />
                <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="saveBreedAndOrigin"><x-ui-icon name="save" /><span wire:loading.remove wire:target="saveBreedAndOrigin">{{ __('pet_profiles.actions.save_step') }}</span><span wire:loading wire:target="saveBreedAndOrigin">{{ __('pet_profiles.actions.saving') }}</span></button>
            </form>
        @elseif ($activeStep['value'] === 'appearance')
            <form wire:submit="saveAppearance" wire:change="autoSaveStep('appearance', $event.currentTarget.dataset.petProfileAutosaveRevision)" class="forum-form mt-6" data-pet-profile-autosave-step="appearance">
                <div class="grid min-w-0 gap-4 md:grid-cols-2">
                    <label class="forum-form__field" for="managed-pet-appearance">
                        <span>{{ __('pet_profiles.fields.appearance_summary') }}</span>
                        <textarea id="managed-pet-appearance" wire:model="form.appearanceSummary" rows="5" maxlength="1500" aria-describedby="managed-pet-appearance-help managed-pet-appearance-error" @error('form.appearanceSummary') aria-invalid="true" @enderror></textarea>
                        <small id="managed-pet-appearance-help">{{ __('pet_profiles.completion.help.appearance') }}</small>
                        @error('form.appearanceSummary') <small id="managed-pet-appearance-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field" for="managed-pet-identifying-marks">
                        <span>{{ __('pet_profiles.fields.identifying_marks') }}</span>
                        <textarea id="managed-pet-identifying-marks" wire:model="form.identifyingMarks" rows="5" maxlength="1500" aria-describedby="managed-pet-identifying-marks-help managed-pet-identifying-marks-error" @error('form.identifyingMarks') aria-invalid="true" @enderror></textarea>
                        <small id="managed-pet-identifying-marks-help">{{ __('pet_profiles.completion.help.identifying_marks') }}</small>
                        @error('form.identifyingMarks') <small id="managed-pet-identifying-marks-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                </div>
                <x-pet-profile-save-status :feedback="$feedback" />
                <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="saveAppearance"><x-ui-icon name="save" /><span wire:loading.remove wire:target="saveAppearance">{{ __('pet_profiles.actions.save_step') }}</span><span wire:loading wire:target="saveAppearance">{{ __('pet_profiles.actions.saving') }}</span></button>
            </form>
        @elseif ($activeStep['value'] === 'character')
            <form wire:submit="saveCharacter" wire:change="autoSaveStep('character', $event.currentTarget.dataset.petProfileAutosaveRevision)" class="forum-form mt-6" data-pet-profile-autosave-step="character">
                <div class="grid min-w-0 gap-4 md:grid-cols-2">
                    <label class="forum-form__field" for="managed-pet-bio">
                        <span>{{ __('pet_profiles.fields.bio') }}</span>
                        <textarea id="managed-pet-bio" wire:model="form.bio" rows="6" maxlength="3000" aria-describedby="managed-pet-bio-help managed-pet-bio-error" @error('form.bio') aria-invalid="true" @enderror></textarea>
                        <small id="managed-pet-bio-help">{{ __('pet_profiles.completion.help.bio') }}</small>
                        @error('form.bio') <small id="managed-pet-bio-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field" for="managed-pet-temperament">
                        <span>{{ __('pet_profiles.fields.temperament_summary') }}</span>
                        <textarea id="managed-pet-temperament" wire:model="form.temperamentSummary" rows="6" maxlength="1500" aria-describedby="managed-pet-temperament-help managed-pet-temperament-error" @error('form.temperamentSummary') aria-invalid="true" @enderror></textarea>
                        <small id="managed-pet-temperament-help">{{ __('pet_profiles.completion.help.temperament') }}</small>
                        @error('form.temperamentSummary') <small id="managed-pet-temperament-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                </div>
                <x-pet-profile-save-status :feedback="$feedback" />
                <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="saveCharacter"><x-ui-icon name="save" /><span wire:loading.remove wire:target="saveCharacter">{{ __('pet_profiles.actions.save_step') }}</span><span wire:loading wire:target="saveCharacter">{{ __('pet_profiles.actions.saving') }}</span></button>
            </form>
        @elseif ($activeStep['value'] === 'social-preferences')
            <form wire:submit="saveSocialPreferences" wire:change="autoSaveStep('social-preferences', $event.currentTarget.dataset.petProfileAutosaveRevision)" class="forum-form mt-6" data-pet-profile-autosave-step="social-preferences">
                <x-notice
                    icon="heart-handshake"
                    :title="__('pet_profiles.completion.social_notice_title')"
                    :description="__('pet_profiles.completion.social_notice')"
                />
                <div class="grid min-w-0 gap-4 md:grid-cols-2">
                    <label class="forum-form__field" for="managed-pet-community-preferences">
                        <span>{{ __('pet_profiles.fields.social_preferences') }}</span>
                        <textarea id="managed-pet-community-preferences" wire:model="form.socialPreferences" rows="6" maxlength="1500" aria-describedby="managed-pet-community-preferences-help managed-pet-community-preferences-error" @error('form.socialPreferences') aria-invalid="true" @enderror></textarea>
                        <small id="managed-pet-community-preferences-help">{{ __('pet_profiles.completion.help.social_preferences') }}</small>
                        @error('form.socialPreferences') <small id="managed-pet-community-preferences-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field" for="managed-pet-meeting-preferences">
                        <span>{{ __('pet_profiles.fields.meeting_preferences') }}</span>
                        <textarea id="managed-pet-meeting-preferences" wire:model="form.meetingPreferences" rows="6" maxlength="1500" aria-describedby="managed-pet-meeting-preferences-help managed-pet-meeting-preferences-error" @error('form.meetingPreferences') aria-invalid="true" @enderror></textarea>
                        <small id="managed-pet-meeting-preferences-help">{{ __('pet_profiles.completion.help.meeting_preferences') }}</small>
                        @error('form.meetingPreferences') <small id="managed-pet-meeting-preferences-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                </div>
                <x-pet-profile-save-status :feedback="$feedback" />
                <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="saveSocialPreferences"><x-ui-icon name="save" /><span wire:loading.remove wire:target="saveSocialPreferences">{{ __('pet_profiles.actions.save_step') }}</span><span wire:loading wire:target="saveSocialPreferences">{{ __('pet_profiles.actions.saving') }}</span></button>
            </form>
        @elseif ($activeStep['value'] === 'location')
            <form wire:submit="saveLocation" wire:change="autoSaveStep('location', $event.currentTarget.dataset.petProfileAutosaveRevision)" class="forum-form mt-6" data-pet-profile-autosave-step="location">
                <x-notice
                    icon="map-pin"
                    :title="__('pet_profiles.completion.location_notice_title')"
                    :description="__('pet_profiles.completion.location_notice')"
                />
                <div class="grid min-w-0 gap-4 md:grid-cols-2">
                    <label class="forum-form__field" for="managed-pet-location-label">
                        <span>{{ __('pet_profiles.fields.public_location') }}</span>
                        <input id="managed-pet-location-label" type="text" wire:model="form.locationLabel" maxlength="120" autocomplete="address-level2" aria-describedby="managed-pet-location-label-help managed-pet-location-label-error" @error('form.locationLabel') aria-invalid="true" @enderror>
                        <small id="managed-pet-location-label-help">{{ __('pet_profiles.completion.help.location') }}</small>
                        @error('form.locationLabel') <small id="managed-pet-location-label-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field" for="managed-pet-location-precision">
                        <span>{{ __('pet_profiles.fields.location_precision') }}</span>
                        <select id="managed-pet-location-precision" wire:model="form.locationPrecision">
                            @forelse (['hidden', 'country', 'region', 'city', 'district'] as $value)
                                <option wire:key="managed-location-precision-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.location_precision.'.$value) }}</option>
                            @empty
                                <option value="hidden">{{ __('pet_profiles.location_precision.hidden') }}</option>
                            @endforelse
                        </select>
                    </label>
                </div>
                <x-pet-profile-save-status :feedback="$feedback" />
                <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="saveLocation"><x-ui-icon name="save" /><span wire:loading.remove wire:target="saveLocation">{{ __('pet_profiles.actions.save_step') }}</span><span wire:loading wire:target="saveLocation">{{ __('pet_profiles.actions.saving') }}</span></button>
            </form>
        @elseif ($activeStep['value'] === 'owners')
            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-paw-line bg-white p-4">
                <div>
                    <h3>{{ __('pet_profiles.access_requests.pending_title') }}</h3>
                    <p class="mt-1 text-sm text-paw-muted">{{ __('pet_profiles.access_requests.manager_link_description') }}</p>
                </div>
                <a class="forum-button min-h-11" href="{{ route('pets.manage.access-requests', ['petProfile' => $profile->profile_key]) }}">
                    <x-ui-icon name="users" />
                    <span>{{ __('pet_profiles.access_requests.review_action') }}</span>
                </a>
            </div>
            <div class="mt-6 grid gap-3">
                @forelse ($this->managers as $manager)
                    <article class="forum-form" wire:key="pet-manager-{{ $manager['id'] }}">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="break-words">{{ $manager['name'] }}</h3>
                                <p>{{ $manager['role'] }} · {{ $manager['status'] }}</p>
                                @if ($manager['ends_at'] !== null)
                                    <p class="text-sm text-paw-muted">{{ __('pet_profiles.managers.ends', ['date' => $manager['ends_at']]) }}</p>
                                @endif
                            </div>
                            @if ($manager['revocable'])
                                <button type="button" class="forum-button min-h-11" wire:click="revokeManager({{ $manager['id'] }})" wire:confirm="{{ __('pet_profiles.confirmations.revoke_manager') }}"><x-ui-icon name="user-x" /><span>{{ __('pet_profiles.actions.revoke') }}</span></button>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="forum-form">{{ __('pet_profiles.managers.empty') }}</p>
                @endforelse
            </div>
            <form wire:submit="inviteManager" class="forum-form mt-4">
                <h3>{{ __('pet_profiles.managers.invite') }}</h3>
                <div class="grid min-w-0 gap-4 md:grid-cols-3">
                    <label class="forum-form__field" for="pet-manager-email">
                        <span>{{ __('pet_profiles.fields.email') }}</span>
                        <input id="pet-manager-email" type="email" wire:model="invitationForm.email" autocomplete="email" required aria-describedby="pet-manager-email-error" @error('invitationForm.email') aria-invalid="true" @enderror>
                        @error('invitationForm.email') <small id="pet-manager-email-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field" for="pet-manager-role">
                        <span>{{ __('pet_profiles.fields.role') }}</span>
                        <select id="pet-manager-role" wire:model="invitationForm.role">
                            @forelse ($this->invitationRoleOptions as $value => $label)
                                <option wire:key="invite-role-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option value="other">{{ __('pet_profiles.manager_roles.other') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="forum-form__field" for="pet-manager-ends-at">
                        <span>{{ __('pet_profiles.fields.ends_at') }}</span>
                        <input id="pet-manager-ends-at" type="datetime-local" wire:model="invitationForm.endsAt" min="{{ $managerMinimumEnd }}">
                    </label>
                </div>
                <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="inviteManager"><x-ui-icon name="user-plus" /><span>{{ __('pet_profiles.actions.invite') }}</span></button>
            </form>
        @elseif ($activeStep['value'] === 'privacy')
            <form wire:submit="savePrivacy" class="forum-form mt-6">
                <div class="grid min-w-0 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <label class="forum-form__field" for="pet-profile-privacy"><span>{{ __('pet_profiles.fields.profile_visibility') }}</span><select id="pet-profile-privacy" wire:model="privacyForm.profileVisibility">@forelse ($this->visibilityOptions as $value => $label)<option wire:key="profile-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>@empty<option value="private">{{ __('pet_profiles.visibility.private') }}</option>@endforelse</select></label>
                    <label class="forum-form__field" for="pet-location-privacy"><span>{{ __('pet_profiles.fields.location_visibility') }}</span><select id="pet-location-privacy" wire:model="privacyForm.locationVisibility">@forelse ($this->visibilityOptions as $value => $label)<option wire:key="location-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>@empty<option value="private">{{ __('pet_profiles.visibility.private') }}</option>@endforelse</select></label>
                    <label class="forum-form__field" for="pet-posts-privacy"><span>{{ __('pet_profiles.fields.posts_visibility') }}</span><select id="pet-posts-privacy" wire:model="privacyForm.postsVisibility">@forelse ($this->visibilityOptions as $value => $label)<option wire:key="posts-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>@empty<option value="private">{{ __('pet_profiles.visibility.private') }}</option>@endforelse</select></label>
                    <label class="forum-form__field" for="pet-friends-privacy"><span>{{ __('pet_profiles.fields.friends_visibility') }}</span><select id="pet-friends-privacy" wire:model="privacyForm.friendsVisibility">@forelse ($this->visibilityOptions as $value => $label)<option wire:key="friends-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>@empty<option value="private">{{ __('pet_profiles.visibility.private') }}</option>@endforelse</select></label>
                    <label class="forum-form__field" for="pet-care-privacy"><span>{{ __('pet_profiles.fields.care_visibility') }}</span><select id="pet-care-privacy" wire:model="privacyForm.careVisibility">@forelse ($this->visibilityOptions as $value => $label)<option wire:key="care-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>@empty<option value="private">{{ __('pet_profiles.visibility.private') }}</option>@endforelse</select></label>
                    <label class="forum-form__field" for="pet-activity-privacy"><span>{{ __('pet_profiles.fields.activity_visibility') }}</span><select id="pet-activity-privacy" wire:model="privacyForm.activityVisibility">@forelse ($this->visibilityOptions as $value => $label)<option wire:key="activity-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>@empty<option value="private">{{ __('pet_profiles.visibility.private') }}</option>@endforelse</select></label>
                    <label class="forum-form__field" for="pet-owner-display"><span>{{ __('pet_profiles.fields.owner_display') }}</span><select id="pet-owner-display" wire:model="privacyForm.ownerDisplayMode">@forelse (['full', 'public-name', 'username', 'organization', 'contact-button', 'hidden'] as $value)<option wire:key="owner-display-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.owner_display.'.$value) }}</option>@empty<option value="hidden">{{ __('pet_profiles.owner_display.hidden') }}</option>@endforelse</select></label>
                    <label class="forum-form__field" for="pet-manager-display"><span>{{ __('pet_profiles.fields.manager_display') }}</span><select id="pet-manager-display" wire:model="privacyForm.managerDisplayMode">@forelse (['all', 'primary', 'organization', 'count', 'hidden'] as $value)<option wire:key="manager-display-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.manager_display.'.$value) }}</option>@empty<option value="hidden">{{ __('pet_profiles.manager_display.hidden') }}</option>@endforelse</select></label>
                    <label class="forum-form__field" for="pet-location-precision"><span>{{ __('pet_profiles.fields.location_precision') }}</span><select id="pet-location-precision" wire:model="privacyForm.publicLocationPrecision">@forelse (['country', 'city', 'district', 'region', 'hidden'] as $value)<option wire:key="location-precision-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.location_precision.'.$value) }}</option>@empty<option value="hidden">{{ __('pet_profiles.location_precision.hidden') }}</option>@endforelse</select></label>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="forum-form__check"><input type="checkbox" wire:model="privacyForm.isDiscoverable"><span>{{ __('pet_profiles.fields.discoverable') }}</span></label>
                    <label class="forum-form__check"><input type="checkbox" wire:model="privacyForm.allowDirectLink"><span>{{ __('pet_profiles.fields.direct_link') }}</span></label>
                    <label class="forum-form__check"><input type="checkbox" wire:model="privacyForm.allowExternalIndexing"><span>{{ __('pet_profiles.fields.external_indexing') }}</span></label>
                </div>
                <p class="text-sm text-paw-muted" wire:dirty wire:target="privacyForm">{{ __('pet_profiles.feedback.unsaved') }}</p>
                <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="savePrivacy"><x-ui-icon name="shield-check" /><span wire:loading.remove wire:target="savePrivacy">{{ __('pet_profiles.actions.save_privacy') }}</span><span wire:loading wire:target="savePrivacy">{{ __('pet_profiles.actions.saving') }}</span></button>
            </form>
        @elseif ($activeStep['value'] === 'documents')
            @if ($canManageDocuments)
                <form wire:submit="saveDocuments" class="forum-form mt-6">
                <x-notice
                    icon="shield-alert"
                    :title="__('pet_profiles.completion.documents_notice_title')"
                    :description="__('pet_profiles.completion.documents_notice')"
                />
                <div class="grid min-w-0 gap-4 md:grid-cols-2">
                    <label class="forum-form__field" for="pet-microchip-status">
                        <span>{{ __('pet_profiles.fields.microchip_status') }}</span>
                        <select id="pet-microchip-status" wire:model.live="documentsForm.microchipStatus">
                            @forelse (['unknown', 'not-chipped', 'chipped-identifier-unknown', 'chipped'] as $value)
                                <option wire:key="microchip-status-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.microchip_status.'.$value) }}</option>
                            @empty
                                <option value="unknown">{{ __('pet_profiles.microchip_status.unknown') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="forum-form__field" for="pet-microchip-identifier">
                        <span>{{ __('pet_profiles.fields.microchip_identifier') }}</span>
                        <input id="pet-microchip-identifier" type="text" wire:model="documentsForm.microchipIdentifier" maxlength="80" autocomplete="off" aria-describedby="pet-microchip-identifier-help pet-microchip-identifier-error" @disabled($documentsForm->microchipStatus !== 'chipped') @error('documentsForm.microchipIdentifier') aria-invalid="true" @enderror>
                        <small id="pet-microchip-identifier-help">
                            {{ $hasMicrochipIdentifier
                                ? __('pet_profiles.completion.help.microchip_identifier_existing')
                                : __('pet_profiles.completion.help.microchip_identifier') }}
                        </small>
                        @error('documentsForm.microchipIdentifier') <small id="pet-microchip-identifier-error" role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field md:col-span-2" for="pet-documents-state">
                        <span>{{ __('pet_profiles.fields.documents_state') }}</span>
                        <select id="pet-documents-state" wire:model="documentsForm.documentsState">
                            @forelse (['none', 'available', 'add-later', 'not-applicable'] as $value)
                                <option wire:key="documents-state-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.documents_state.'.$value) }}</option>
                            @empty
                                <option value="add-later">{{ __('pet_profiles.documents_state.add-later') }}</option>
                            @endforelse
                        </select>
                    </label>
                </div>
                <p class="text-sm text-paw-muted" wire:dirty wire:target="documentsForm">{{ __('pet_profiles.feedback.unsaved') }}</p>
                <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="saveDocuments"><x-ui-icon name="lock-keyhole" /><span wire:loading.remove wire:target="saveDocuments">{{ __('pet_profiles.actions.save_documents') }}</span><span wire:loading wire:target="saveDocuments">{{ __('pet_profiles.actions.saving') }}</span></button>
                </form>
            @else
                <div class="mt-6">
                    <x-notice
                        icon="lock-keyhole"
                        :title="__('pet_profiles.completion.documents_unavailable_title')"
                        :description="__('pet_profiles.completion.documents_unavailable')"
                    />
                </div>
            @endif
        @elseif ($activeStep['value'] === 'preview')
            <div class="mt-6 grid gap-5">
                <section class="grid gap-4 rounded-2xl border border-paw-line bg-paw-canvas p-4 sm:grid-cols-[minmax(0,1fr)_9rem] sm:items-center" aria-labelledby="pet-stable-link-heading">
                    <div>
                        <h3 id="pet-stable-link-heading">{{ __('pet_profiles.manage.stable_link') }}</h3>
                        <p class="mt-2 break-all text-sm text-paw-muted">{{ $profileUrl }}</p>
                        <a class="forum-button mt-4 min-h-11" href="{{ $profileUrl }}"><x-ui-icon name="external-link" />{{ __('pet_profiles.actions.view_profile') }}</a>
                    </div>
                    @if ($qrCode !== null)
                        <img src="{{ $qrCode }}" alt="{{ __('pet_profiles.manage.qr_alt', ['name' => $profile->name]) }}" class="size-36 bg-white p-2">
                    @endif
                </section>
                <form wire:submit="transitionStatus" class="forum-form">
                    <h3>{{ __('pet_profiles.manage.lifecycle') }}</h3>
                    <p class="text-sm text-paw-muted">{{ __('pet_profiles.completion.current_status', ['status' => $currentStatusLabel]) }}</p>
                    <div class="grid min-w-0 gap-4 md:grid-cols-2">
                        <label class="forum-form__field" for="pet-target-status"><span>{{ __('pet_profiles.fields.status') }}</span><select id="pet-target-status" wire:model="targetStatus">@forelse ($this->statusOptions as $value => $label)<option wire:key="pet-status-{{ $value }}" value="{{ $value }}">{{ $label }}</option>@empty<option value="{{ $profile->status->value }}">{{ $currentStatusLabel }}</option>@endforelse</select></label>
                        <label class="forum-form__field" for="pet-status-reason"><span>{{ __('pet_profiles.fields.reason') }}</span><input id="pet-status-reason" type="text" wire:model="statusReason" maxlength="500"></label>
                    </div>
                    <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="transitionStatus" @disabled($targetStatus === $profile->status->value)><x-ui-icon name="refresh-cw" /><span>{{ __('pet_profiles.actions.change_status') }}</span></button>
                </form>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[36rem] border-collapse text-left">
                        <caption class="sr-only">{{ __('pet_profiles.history.caption') }}</caption>
                        <thead><tr class="border-b border-paw-line"><th scope="col" class="px-3 py-2">{{ __('pet_profiles.history.event') }}</th><th scope="col" class="px-3 py-2">{{ __('pet_profiles.history.actor') }}</th><th scope="col" class="px-3 py-2">{{ __('pet_profiles.history.time') }}</th></tr></thead>
                        <tbody>
                            @forelse ($this->history as $event)
                                <tr class="border-b border-paw-line" wire:key="pet-history-{{ $event['id'] }}"><td class="px-3 py-2">{{ $event['event'] }}</td><td class="px-3 py-2">{{ $event['actor'] }}</td><td class="px-3 py-2"><time>{{ $event['occurred_at'] }}</time></td></tr>
                            @empty
                                <tr><td colspan="3" class="px-3 py-4 text-paw-muted">{{ __('pet_profiles.history.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>

    <p class="rounded-xl border border-status-warning/40 bg-status-warning/10 px-4 py-3" wire:offline role="status">
        {{ __('pet_profiles.feedback.offline') }}
    </p>
</x-page-stack>
