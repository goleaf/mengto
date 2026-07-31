<div class="forum-page" aria-labelledby="knowledge-guide-editor-heading">
    <header class="forum-header">
        <div class="forum-header__copy">
            <p class="forum-header__eyebrow">{{ __('knowledge.editor.eyebrow') }}</p>
            <h1 id="knowledge-guide-editor-heading">
                @if ($articleId !== null)
                    {{ __('knowledge.editor.edit_title') }}
                @elseif ($this->translationSourceData !== null)
                    {{ __('knowledge.editor.translate_title') }}
                @else
                    {{ __('knowledge.editor.create_title') }}
                @endif
            </h1>
            <p>{{ __('knowledge.editor.description') }}</p>
        </div>
        <div class="forum-header__actions">
            <a href="{{ route('knowledge.index') }}" class="forum-button">
                <x-lucide-arrow-left aria-hidden="true" />
                {{ __('knowledge.actions.library') }}
            </a>
            @if ($this->articleData['slug'] !== null && in_array($this->articleData['status'], ['published', 'outdated'], true))
                <a href="{{ route('knowledge.articles.show', $this->articleData['slug']) }}" class="forum-button">
                    <x-lucide-external-link aria-hidden="true" />
                    {{ __('knowledge.actions.view_public') }}
                </a>
            @endif
        </div>
    </header>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <p wire:offline class="border-s-4 border-status-warning py-3 ps-4" role="status">
        {{ __('knowledge.editor.offline') }}
    </p>

    @if ($this->translationSourceData !== null)
        <aside class="forum-safety" aria-labelledby="knowledge-translation-source-heading">
            <x-lucide-languages aria-hidden="true" />
            <div>
                <strong id="knowledge-translation-source-heading">
                    {{ __('knowledge.translations.source_heading') }}
                </strong>
                <span>
                    {{ __('knowledge.translations.source_description', [
                        'title' => $this->translationSourceData['title'],
                        'language' => $this->translationSourceData['locale_label'],
                    ]) }}
                </span>
                <span>{{ __('knowledge.translations.original_preserved') }}</span>
            </div>
        </aside>
    @endif

    <div class="flex flex-wrap items-center gap-3 border-y border-paw-line py-4">
        <x-status-badge :label="$this->articleData['status_label']" icon="book-open-check" />
        @if ($articleId !== null)
            <span class="text-sm text-paw-muted">
                {{ __('knowledge.editor.current_version', ['version' => $this->articleData['version']]) }}
            </span>
        @endif
        <span wire:dirty class="text-sm font-semibold text-status-warning">
            {{ __('knowledge.editor.unsaved') }}
        </span>
    </div>

    @if ($this->articleData['is_locked'])
        <aside class="forum-safety" role="status">
            <x-lucide-lock-keyhole aria-hidden="true" />
            <div>
                <strong>{{ __('knowledge.editor.locked_title') }}</strong>
                <span>{{ $this->articleData['lock_reason'] ?? __('knowledge.editor.locked_description') }}</span>
            </div>
        </aside>
    @endif

    <form wire:submit="save" class="forum-form knowledge-guide-editor">
        @if ($errors->any())
            <x-forum-error-summary
                :messages="$errors->getMessages()"
                :heading="__('knowledge.validation.summary')"
            />
        @endif

        <section aria-labelledby="knowledge-guide-content-heading">
            <h2 id="knowledge-guide-content-heading">{{ __('knowledge.editor.content_heading') }}</h2>
            <div class="mt-4 grid gap-4">
                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.title') }}</span>
                    <input type="text" wire:model="form.title" maxlength="180" autocomplete="off">
                    @error('form.title') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.summary') }}</span>
                    <textarea wire:model="form.summary" rows="3" maxlength="1500"></textarea>
                    @error('form.summary') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.body') }}</span>
                    <textarea wire:model="form.body" rows="18" maxlength="100000"></textarea>
                    <small>{{ __('knowledge.editor.plain_text_notice') }}</small>
                    @error('form.body') <small role="alert">{{ $message }}</small> @enderror
                </label>
            </div>
        </section>

        <section class="border-t border-paw-line pt-5" aria-labelledby="knowledge-guide-scope-heading">
            <h2 id="knowledge-guide-scope-heading">{{ __('knowledge.editor.scope_heading') }}</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.category') }}</span>
                    <select wire:model="form.category">
                        @forelse ($this->categoryOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('knowledge.empty.categories') }}</option>
                        @endforelse
                    </select>
                    @error('form.category') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.type') }}</span>
                    <select wire:model="form.type">
                        @forelse ($this->typeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('knowledge.empty.types') }}</option>
                        @endforelse
                    </select>
                    @error('form.type') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.difficulty') }}</span>
                    <select wire:model="form.difficulty">
                        @forelse ($this->difficultyOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('knowledge.empty.difficulties') }}</option>
                        @endforelse
                    </select>
                    @error('form.difficulty') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.language') }}</span>
                    <select wire:model="form.language">
                        @forelse ($this->localeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('knowledge.empty.locales') }}</option>
                        @endforelse
                    </select>
                    @if ($this->translationSourceData !== null)
                        <small>{{ __('knowledge.translations.target_locale_help') }}</small>
                    @endif
                    @error('form.language') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.audience') }}</span>
                    <input type="text" wire:model="form.audience" maxlength="120">
                    @error('form.audience') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.jurisdiction') }}</span>
                    <input type="text" wire:model="form.jurisdiction" maxlength="120">
                    @error('form.jurisdiction') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.discussion') }}</span>
                    <input type="number" wire:model="form.discussionTopicId" min="1">
                    @error('form.discussionTopicId') <small role="alert">{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="mt-4">
                <span class="mb-2 block text-sm font-semibold">{{ __('knowledge.fields.taxon') }}</span>
                <livewire:forum.animal-taxonomy-selector
                    wire:model="form.taxonIds"
                    :selected="$form->taxonIds"
                    input-name="taxon_id"
                    :selection-limit="1"
                />
                @error('form.taxonIds') <small role="alert">{{ $message }}</small> @enderror
                @error('form.taxonIds.*') <small role="alert">{{ $message }}</small> @enderror
            </div>
        </section>

        <section class="border-t border-paw-line pt-5" aria-labelledby="knowledge-guide-sources-heading">
            <h2 id="knowledge-guide-sources-heading">{{ __('knowledge.editor.sources_heading') }}</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.sources') }}</span>
                    <textarea wire:model="form.sourcesText" rows="6" maxlength="5000"></textarea>
                    <small>{{ __('knowledge.editor.sources_help') }}</small>
                    @error('form.sourcesText') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <label class="forum-form__field">
                    <span>{{ __('knowledge.fields.protected_sections') }}</span>
                    <textarea wire:model="form.protectedSectionsText" rows="6" maxlength="3000"></textarea>
                    <small>{{ __('knowledge.editor.protected_sections_help') }}</small>
                    @error('form.protectedSectionsText') <small role="alert">{{ $message }}</small> @enderror
                </label>
            </div>
        </section>

        <label class="forum-form__field border-t border-paw-line pt-5">
            <span>{{ __('knowledge.fields.change_summary') }}</span>
            <input type="text" wire:model="form.changeSummary" maxlength="240">
            @error('form.changeSummary') <small role="alert">{{ $message }}</small> @enderror
        </label>

        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="forum-button forum-button--primary w-fit">
            <x-lucide-save aria-hidden="true" />
            <span wire:loading.remove wire:target="save">
                @if ($articleId !== null)
                    {{ __('knowledge.actions.save_revision') }}
                @elseif ($this->translationSourceData !== null)
                    {{ __('knowledge.actions.create_translation_draft') }}
                @else
                    {{ __('knowledge.actions.create_draft') }}
                @endif
            </span>
            <span wire:loading wire:target="save">{{ __('knowledge.actions.saving') }}</span>
        </button>
    </form>

    @if ($articleId !== null)
        <div class="grid gap-8 border-t border-paw-line pt-8 xl:grid-cols-2">
            <section aria-labelledby="knowledge-workflow-heading">
                <h2 id="knowledge-workflow-heading">{{ __('knowledge.workflow.heading') }}</h2>
                @if ($this->workflowOptions !== [])
                    <form wire:submit="applyWorkflowTransition" class="forum-form mt-4 grid gap-4">
                        <label class="forum-form__field">
                            <span>{{ __('knowledge.fields.next_status') }}</span>
                            <select wire:model="workflowStatus">
                                @forelse ($this->workflowOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @empty
                                    <option disabled>{{ __('knowledge.workflow.no_transitions') }}</option>
                                @endforelse
                            </select>
                            @error('workflowStatus') <small role="alert">{{ $message }}</small> @enderror
                        </label>

                        @if ($workflowStatus === 'replaced')
                            <label class="forum-form__field">
                                <span>{{ __('knowledge.fields.replacement') }}</span>
                                <select wire:model="replacementArticleId">
                                    <option value="">{{ __('knowledge.actions.select_replacement') }}</option>
                                    @forelse ($this->replacementOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                        <option disabled>{{ __('knowledge.empty.replacements') }}</option>
                                    @endforelse
                                </select>
                                @error('replacementArticleId') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                        @endif

                        <label class="forum-form__field">
                            <span>{{ __('knowledge.fields.workflow_reason') }}</span>
                            <textarea wire:model="workflowReason" rows="3" maxlength="500"></textarea>
                            @error('workflowReason') <small role="alert">{{ $message }}</small> @enderror
                        </label>

                        <button type="submit" wire:loading.attr="disabled" wire:target="applyWorkflowTransition" class="forum-button forum-button--primary w-fit">
                            <x-lucide-git-branch aria-hidden="true" />
                            <span wire:loading.remove wire:target="applyWorkflowTransition">{{ __('knowledge.actions.apply_transition') }}</span>
                            <span wire:loading wire:target="applyWorkflowTransition">{{ __('knowledge.actions.updating') }}</span>
                        </button>
                    </form>
                @else
                    <p class="mt-3 text-paw-muted">{{ __('knowledge.workflow.no_transitions') }}</p>
                @endif
            </section>

            @if ($this->canManageWorkflow)
                <section aria-labelledby="knowledge-editorial-lock-heading">
                    <h2 id="knowledge-editorial-lock-heading">{{ __('knowledge.lock.heading') }}</h2>
                    @if ($this->articleData['is_locked'])
                        <p class="mt-3 text-paw-muted">{{ __('knowledge.lock.active') }}</p>
                        <button type="button" wire:click="setEditorialLock(false)" wire:loading.attr="disabled" wire:target="setEditorialLock" class="forum-button mt-4">
                            <x-lucide-lock-open aria-hidden="true" />
                            {{ __('knowledge.actions.unlock') }}
                        </button>
                    @else
                        <label class="forum-form__field mt-4">
                            <span>{{ __('knowledge.fields.lock_reason') }}</span>
                            <textarea wire:model="editorialLockReason" rows="3" maxlength="500"></textarea>
                            @error('editorialLockReason') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <button type="button" wire:click="setEditorialLock(true)" wire:loading.attr="disabled" wire:target="setEditorialLock" class="forum-button mt-4">
                            <x-lucide-lock-keyhole aria-hidden="true" />
                            {{ __('knowledge.actions.lock') }}
                        </button>
                    @endif
                </section>
            @endif
        </div>

        @if ($this->canManageWorkflow)
            <section class="border-t border-paw-line pt-8" aria-labelledby="knowledge-collaborators-heading">
                <h2 id="knowledge-collaborators-heading">{{ __('knowledge.collaborators.heading') }}</h2>
                <div class="mt-4 grid gap-3">
                    @forelse ($this->collaborators as $collaborator)
                        <div wire:key="knowledge-collaborator-{{ $collaborator['id'] }}" class="flex flex-wrap items-center justify-between gap-3 border-b border-paw-line py-3">
                            <div>
                                <strong>{{ $collaborator['name'] }}</strong>
                                <p class="text-sm text-paw-muted">{{ $collaborator['role_label'] }} · {{ $collaborator['email'] }}</p>
                            </div>
                            <button
                                type="button"
                                wire:click="revokeCollaborator({{ $collaborator['id'] }})"
                                wire:confirm="{{ __('knowledge.actions.remove_collaborator_confirm') }}"
                                wire:loading.attr="disabled"
                                wire:target="revokeCollaborator"
                                class="forum-button"
                                aria-label="{{ __('knowledge.actions.remove_collaborator_named', ['name' => $collaborator['name']]) }}"
                            >
                                <x-lucide-user-round-minus aria-hidden="true" />
                                {{ __('knowledge.actions.remove') }}
                            </button>
                        </div>
                    @empty
                        <p class="text-paw-muted">{{ __('knowledge.empty.collaborators') }}</p>
                    @endforelse
                </div>

                <form wire:submit="addCollaborator" class="forum-form mt-5 grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(12rem,0.5fr)_auto] md:items-end">
                    <label class="forum-form__field">
                        <span>{{ __('knowledge.fields.collaborator_email') }}</span>
                        <input type="email" wire:model="collaboratorEmail" maxlength="255" autocomplete="email">
                        @error('collaboratorEmail') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('knowledge.fields.collaborator_role') }}</span>
                        <select wire:model="collaboratorRole">
                            @forelse ($this->collaboratorRoleOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('knowledge.empty.roles') }}</option>
                            @endforelse
                        </select>
                        @error('collaboratorRole') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <button type="submit" wire:loading.attr="disabled" wire:target="addCollaborator" class="forum-button forum-button--primary">
                        <x-lucide-user-round-plus aria-hidden="true" />
                        {{ __('knowledge.actions.add') }}
                    </button>
                </form>
            </section>

            <section class="border-t border-paw-line pt-8" aria-labelledby="knowledge-corrections-heading">
                <h2 id="knowledge-corrections-heading">{{ __('knowledge.corrections.heading') }}</h2>
                <div class="mt-4 grid gap-4">
                    @forelse ($this->corrections as $correction)
                        <div wire:key="knowledge-correction-{{ $correction['id'] }}" class="grid gap-2 border-b border-paw-line py-4">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="radio" wire:model="selectedCorrectionId" value="{{ $correction['id'] }}" class="mt-1 size-5">
                                <span>
                                    <strong>{{ $correction['field'] }} · {{ $correction['reporter'] }}</strong>
                                    <span class="mt-1 block whitespace-pre-line text-sm text-paw-muted">{{ $correction['suggestion'] }}</span>
                                </span>
                            </label>
                            @if ($correction['source_url'])
                                <a href="{{ $correction['source_url'] }}" target="_blank" rel="noopener noreferrer" class="text-sm underline">
                                    {{ __('knowledge.actions.open_correction_source') }}
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-paw-muted">{{ __('knowledge.empty.corrections') }}</p>
                    @endforelse
                </div>

                @if ($this->corrections !== [])
                    <form wire:submit="reviewCorrection" class="forum-form mt-5 grid gap-4 md:grid-cols-2">
                        <label class="forum-form__field">
                            <span>{{ __('knowledge.fields.correction_decision') }}</span>
                            <select wire:model="correctionDecision">
                                <option value="accepted">{{ __('knowledge.correction_status.accepted') }}</option>
                                <option value="rejected">{{ __('knowledge.correction_status.rejected') }}</option>
                                <option value="applied">{{ __('knowledge.correction_status.applied') }}</option>
                            </select>
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('knowledge.fields.correction_reason') }}</span>
                            <textarea wire:model="correctionReason" rows="3" maxlength="500"></textarea>
                            @error('correctionReason') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        @error('selectedCorrectionId') <p role="alert">{{ $message }}</p> @enderror
                        <button type="submit" wire:loading.attr="disabled" wire:target="reviewCorrection" class="forum-button forum-button--primary w-fit">
                            <x-lucide-clipboard-check aria-hidden="true" />
                            {{ __('knowledge.actions.review_correction') }}
                        </button>
                    </form>
                @endif
            </section>
        @endif

        <section class="border-t border-paw-line pt-8" aria-labelledby="knowledge-versions-heading">
            <h2 id="knowledge-versions-heading">{{ __('knowledge.versions.heading') }}</h2>
            <div class="mt-4 grid gap-3">
                @forelse ($this->versions as $version)
                    <div wire:key="knowledge-version-{{ $version['id'] }}" class="flex flex-wrap items-center justify-between gap-3 border-b border-paw-line py-3">
                        <div>
                            <strong>{{ __('knowledge.editor.current_version', ['version' => $version['number']]) }}</strong>
                            <p class="text-sm text-paw-muted">{{ $version['summary'] }} · {{ $version['editor'] }}</p>
                        </div>
                        @if ($this->canManageWorkflow && $version['number'] !== $this->articleData['version'])
                            <label class="flex min-h-11 items-center gap-2">
                                <input type="radio" wire:model="rollbackVersionId" value="{{ $version['id'] }}" class="size-5">
                                <span>{{ __('knowledge.actions.select_version') }}</span>
                            </label>
                        @endif
                    </div>
                @empty
                    <p class="text-paw-muted">{{ __('knowledge.empty.versions') }}</p>
                @endforelse
            </div>

            @if ($this->canManageWorkflow && $this->versions !== [])
                <form wire:submit="rollback" class="forum-form mt-5 grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                    <label class="forum-form__field">
                        <span>{{ __('knowledge.fields.rollback_reason') }}</span>
                        <input type="text" wire:model="rollbackReason" maxlength="240">
                        @error('rollbackVersionId') <small role="alert">{{ $message }}</small> @enderror
                        @error('rollbackReason') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <button
                        type="submit"
                        wire:confirm="{{ __('knowledge.actions.rollback_confirm') }}"
                        wire:loading.attr="disabled"
                        wire:target="rollback"
                        class="forum-button"
                    >
                        <x-lucide-history aria-hidden="true" />
                        {{ __('knowledge.actions.rollback') }}
                    </button>
                </form>
            @endif
        </section>
    @endif
</div>
