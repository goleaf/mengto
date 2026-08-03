<section class="grid gap-6 border-t border-paw-line pt-8" aria-labelledby="forum-journal-timeline-heading">
    <header class="forum-header">
        <div class="forum-header__copy">
            <p class="forum-header__eyebrow">{{ __('forum_journals.timeline.eyebrow') }}</p>
            <h2 id="forum-journal-timeline-heading">{{ __('forum_journals.timeline.heading') }}</h2>
            <p>{{ __('forum_journals.timeline.description') }}</p>
        </div>
    </header>

    <dl class="grid gap-3 border-y border-paw-line py-4 text-sm sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <dt class="font-semibold">{{ __('forum_journals.fields.type') }}</dt>
            <dd>{{ $this->journalData['type'] }}</dd>
        </div>
        <div>
            <dt class="font-semibold">{{ __('forum_journals.fields.status') }}</dt>
            <dd>{{ $this->journalData['status'] }}</dd>
        </div>
        <div>
            <dt class="font-semibold">{{ __('forum_journals.fields.started_on') }}</dt>
            <dd>{{ $this->journalData['started_on'] }}</dd>
        </div>
        <div>
            <dt class="font-semibold">{{ __('forum_journals.fields.owner') }}</dt>
            <dd>{{ $this->journalData['owner_name'] }}</dd>
        </div>
        <div>
            <dt class="font-semibold">{{ __('forum_journals.fields.visibility') }}</dt>
            <dd>{{ $this->journalData['visibility'] }}</dd>
        </div>
    </dl>

    <aside class="border-s-4 border-status-info py-3 ps-4" role="note">
        <p>{{ __('forum_journals.notices.not_medical_record') }}</p>
    </aside>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <p class="hidden border-s-4 border-status-warning py-3 ps-4" wire:offline.class.remove="hidden" role="status">
        {{ __('forum_journals.notices.offline') }}
    </p>

    @if ($this->progressSeries !== [])
        <section aria-labelledby="forum-journal-progress-heading">
            <h3 id="forum-journal-progress-heading">{{ __('forum_journals.progress.heading') }}</h3>
            <p>{{ __('forum_journals.progress.description') }}</p>
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @forelse ($this->progressSeries as $series)
                    <article class="forum-form" wire:key="journal-progress-{{ $series['key'] }}">
                        <h4>{{ $series['label'] }}</h4>
                        <ol class="grid gap-3">
                            @forelse ($series['items'] as $item)
                                <li wire:key="journal-progress-{{ $series['key'] }}-{{ $item['id'] }}">
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span>{{ $item['title'] }}</span>
                                        <strong>{{ $item['value'] }} {{ $series['unit'] }}</strong>
                                    </div>
                                    <progress
                                        class="w-full"
                                        value="{{ $item['value'] }}"
                                        min="{{ $series['min'] }}"
                                        max="{{ $series['max'] }}"
                                        aria-label="{{ __('forum_journals.progress.value_label', ['metric' => $series['label'], 'value' => $item['value'], 'unit' => $series['unit']]) }}"
                                    ></progress>
                                </li>
                            @empty
                                <li>{{ __('forum_journals.empty.measurements') }}</li>
                            @endforelse
                        </ol>
                    </article>
                @empty
                @endforelse
            </div>
        </section>
    @endif

    @if ($this->journalData['can_update'])
        <details class="forum-form" @if ($editingEntryId !== null || $errors->any()) open @endif>
            <summary class="forum-button min-h-11">
                <x-ui-icon name="plus" />
                {{ $editingEntryId === null ? __('forum_journals.actions.add_entry') : __('forum_journals.actions.edit_entry') }}
            </summary>
            <form wire:submit="saveEntry" class="mt-4 grid gap-4" wire:dirty.class="border-status-warning">
                @if ($errors->any())
                    <x-forum-error-summary
                        :messages="$errors->getMessages()"
                        :heading="__('forum_journals.validation.summary')"
                    />
                @endif
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="forum-form__field">
                        <span>{{ __('forum_journals.fields.entry_kind') }}</span>
                        <select wire:model="entryForm.kind" required>
                            @forelse ($this->entryKindOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('forum_journals.empty.entry_kinds') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_journals.fields.occurred_at') }}</span>
                        <input type="datetime-local" wire:model="entryForm.occurredAt" required>
                        @error('entryForm.occurredAt') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                </div>
                <label class="forum-form__field">
                    <span>{{ __('forum_journals.fields.entry_title') }}</span>
                    <input type="text" wire:model="entryForm.title" minlength="2" maxlength="180" required>
                    @error('entryForm.title') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_journals.fields.entry_body') }}</span>
                    <textarea wire:model="entryForm.body" rows="6" minlength="2" maxlength="10000" required></textarea>
                    @error('entryForm.body') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <fieldset class="grid gap-4 md:grid-cols-2">
                    <legend class="font-semibold">{{ __('forum_journals.fields.measurements') }}</legend>
                    @forelse ($this->metricDefinitions as $metric)
                        <label class="forum-form__field">
                            <span>{{ $metric['label'] }} ({{ $metric['unit_label'] }})</span>
                            <input
                                type="number"
                                wire:model="entryForm.metricValues.{{ $metric['key'] }}"
                                min="{{ $metric['min'] }}"
                                max="{{ $metric['max'] }}"
                                step="any"
                            >
                            @error('entryForm.metricValues.'.$metric['key']) <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    @empty
                        <p>{{ __('forum_journals.empty.measurements') }}</p>
                    @endforelse
                </fieldset>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="submit"
                        class="forum-button forum-button--primary min-h-11"
                        wire:loading.attr="disabled"
                        wire:target="saveEntry"
                    >
                        <x-ui-icon name="save" />
                        <span wire:loading.remove wire:target="saveEntry">{{ __('forum_journals.actions.save_entry') }}</span>
                        <span wire:loading wire:target="saveEntry">{{ __('forum_journals.actions.saving') }}</span>
                    </button>
                    @if ($editingEntryId !== null)
                        <button type="button" class="forum-button min-h-11" wire:click="cancelEntryEdit">
                            <x-ui-icon name="x" />
                            {{ __('forum_journals.actions.cancel') }}
                        </button>
                    @endif
                </div>
            </form>
        </details>
    @endif

    <section aria-labelledby="forum-journal-entries-heading">
        <h3 id="forum-journal-entries-heading">{{ __('forum_journals.timeline.entries_heading') }}</h3>
        <div class="mt-4 grid gap-5">
            @forelse ($this->entries as $entry)
                <article class="forum-form" wire:key="forum-journal-entry-{{ $entry['id'] }}">
                    <header class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="forum-header__eyebrow">{{ $entry['kind'] }}</p>
                            <h4 class="text-lg">{{ $entry['title'] }}</h4>
                            <p class="text-sm text-paw-muted">
                                {{ __('forum_journals.labels.entry_by', ['name' => $entry['author_name'], 'date' => $entry['occurred_at']]) }}
                            </p>
                        </div>
                        @if ($this->journalData['can_update'])
                            <button
                                type="button"
                                class="forum-button min-h-11"
                                wire:click="editEntry({{ $entry['id'] }})"
                                aria-label="{{ __('forum_journals.actions.edit_named_entry', ['title' => $entry['title']]) }}"
                            >
                                <x-ui-icon name="pencil" />
                                {{ __('forum_journals.actions.edit') }}
                            </button>
                        @endif
                    </header>

                    <p class="whitespace-pre-line">{{ $entry['body'] }}</p>

                    @if ($entry['measurements'] !== [])
                        <dl class="grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
                            @forelse ($entry['measurements'] as $measurement)
                                <div>
                                    <dt class="font-semibold">{{ $measurement['label'] }}</dt>
                                    <dd>{{ $measurement['value'] }} {{ $measurement['unit'] }}</dd>
                                </div>
                            @empty
                            @endforelse
                        </dl>
                    @endif

                    @if ($entry['media'] !== [])
                        <div class="grid gap-3 sm:grid-cols-2">
                            @forelse ($entry['media'] as $media)
                                <figure wire:key="journal-media-{{ $media['id'] }}">
                                    <img
                                        src="{{ $media['url'] }}"
                                        alt="{{ $media['alt_text'] }}"
                                        class="aspect-video w-full object-cover"
                                        loading="lazy"
                                        width="960"
                                        height="540"
                                    >
                                    @if ($media['caption'])
                                        <figcaption class="mt-2 text-sm">{{ $media['caption'] }}</figcaption>
                                    @endif
                                </figure>
                            @empty
                            @endforelse
                        </div>
                    @endif

                    <section aria-label="{{ __('forum_journals.labels.entry_comments', ['title' => $entry['title']]) }}">
                        <div class="divide-y divide-paw-line border-y border-paw-line">
                            @forelse ($entry['comments'] as $comment)
                                <article class="flex gap-3 py-3" wire:key="journal-comment-{{ $comment['id'] }}">
                                    <span class="forum-topic-card__avatar" aria-hidden="true">{{ $comment['author_initials'] }}</span>
                                    <div>
                                        <strong>{{ $comment['author_name'] }}</strong>
                                        <span class="text-sm text-paw-muted">{{ $comment['created_at'] }}</span>
                                        <p>{{ $comment['body'] }}</p>
                                    </div>
                                </article>
                            @empty
                                <p class="py-3 text-paw-muted">{{ __('forum_journals.empty.comments') }}</p>
                            @endforelse
                        </div>
                    </section>

                    <div class="flex flex-wrap gap-2">
                        @if ($this->journalData['can_comment'])
                            <button type="button" class="forum-button min-h-11" wire:click="beginComment({{ $entry['id'] }})">
                                <x-ui-icon name="message-square-plus" />
                                {{ __('forum_journals.actions.comment') }}
                            </button>
                        @endif
                        @if ($this->journalData['can_update'])
                            <button type="button" class="forum-button min-h-11" wire:click="beginMedia({{ $entry['id'] }})">
                                <x-ui-icon name="image-plus" />
                                {{ __('forum_journals.actions.add_image') }}
                            </button>
                        @endif
                        @if ($entry['version_count'] > 0)
                            <span class="self-center text-sm">
                                {{ trans_choice('forum_journals.labels.version_count', $entry['version_count'], ['count' => $entry['version_count']]) }}
                            </span>
                        @endif
                    </div>

                    @if ($commentEntryId === $entry['id'])
                        <form wire:submit="saveComment" class="grid gap-3 border-s-4 border-paw-line ps-4">
                            <label class="forum-form__field">
                                <span>{{ __('forum_journals.fields.comment') }}</span>
                                <textarea wire:model="commentForm.body" rows="3" minlength="2" maxlength="1500" required></textarea>
                                @error('commentForm.body') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <button type="submit" class="forum-button forum-button--primary min-h-11 justify-self-start" wire:loading.attr="disabled" wire:target="saveComment">
                                <x-ui-icon name="send" />
                                <span wire:loading.remove wire:target="saveComment">{{ __('forum_journals.actions.publish_comment') }}</span>
                                <span wire:loading wire:target="saveComment">{{ __('forum_journals.actions.saving') }}</span>
                            </button>
                        </form>
                    @endif

                    @if ($mediaEntryId === $entry['id'])
                        <form wire:submit="saveMedia" class="grid gap-3 border-s-4 border-paw-line ps-4">
                            <label class="forum-form__field">
                                <span>{{ __('forum_journals.fields.image') }}</span>
                                <input type="file" wire:model="mediaForm.upload" accept="image/jpeg,image/png,image/webp" required>
                                @error('mediaForm.upload') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_journals.fields.alt_text') }}</span>
                                <input type="text" wire:model="mediaForm.altText" minlength="2" maxlength="500" required>
                                @error('mediaForm.altText') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_journals.fields.caption') }}</span>
                                <textarea wire:model="mediaForm.caption" rows="2" maxlength="1000"></textarea>
                                @error('mediaForm.caption') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <button type="submit" class="forum-button forum-button--primary min-h-11 justify-self-start" wire:loading.attr="disabled" wire:target="saveMedia,mediaForm.upload">
                                <x-ui-icon name="upload" />
                                <span wire:loading.remove wire:target="saveMedia,mediaForm.upload">{{ __('forum_journals.actions.upload_image') }}</span>
                                <span wire:loading wire:target="saveMedia,mediaForm.upload">{{ __('forum_journals.actions.uploading') }}</span>
                            </button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="forum-form">
                    <h4>{{ __('forum_journals.empty.entries_title') }}</h4>
                    <p>{{ __('forum_journals.empty.entries_description') }}</p>
                </div>
            @endforelse
        </div>
        <div class="mt-5">{{ $this->entries->links() }}</div>
    </section>

    @if ($this->journalData['can_manage_collaborators'])
        <details class="forum-form">
            <summary class="forum-button min-h-11">
                <x-ui-icon name="users-round" />
                {{ __('forum_journals.collaborators.heading') }}
            </summary>
            <div class="mt-4 grid gap-4">
                <p>{{ __('forum_journals.collaborators.description') }}</p>
                <div class="divide-y divide-paw-line border-y border-paw-line">
                    @forelse ($this->collaborators as $collaborator)
                        <div class="flex flex-wrap items-center justify-between gap-3 py-3" wire:key="journal-collaborator-{{ $collaborator['id'] }}">
                            <div>
                                <strong>{{ $collaborator['name'] }}</strong>
                                <p class="text-sm">{{ $collaborator['email'] }} / {{ $collaborator['role'] }}</p>
                            </div>
                            <button
                                type="button"
                                class="forum-button min-h-11"
                                wire:click="revokeCollaborator({{ $collaborator['id'] }})"
                                wire:confirm="{{ __('forum_journals.actions.revoke_collaborator_confirm') }}"
                                aria-label="{{ __('forum_journals.actions.revoke_named_collaborator', ['name' => $collaborator['name']]) }}"
                            >
                                <x-ui-icon name="user-round-minus" />
                                {{ __('forum_journals.actions.revoke') }}
                            </button>
                        </div>
                    @empty
                        <p class="py-3">{{ __('forum_journals.empty.collaborators') }}</p>
                    @endforelse
                </div>
                <form wire:submit="grantCollaborator" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(10rem,14rem)_auto]">
                    <label class="forum-form__field">
                        <span>{{ __('forum_journals.fields.collaborator_email') }}</span>
                        <input type="email" wire:model="collaboratorForm.email" maxlength="255" required>
                        @error('collaboratorForm.email') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_journals.fields.collaborator_role') }}</span>
                        <select wire:model="collaboratorForm.role" required>
                            @forelse ($this->collaboratorRoleOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('forum_journals.empty.collaborator_roles') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <button type="submit" class="forum-button forum-button--primary min-h-11 self-end" wire:loading.attr="disabled" wire:target="grantCollaborator">
                        <x-ui-icon name="user-round-plus" />
                        {{ __('forum_journals.actions.grant') }}
                    </button>
                </form>
            </div>
        </details>
    @endif

    <div class="flex flex-wrap gap-2">
        @if ($this->journalData['can_export'])
            <a class="forum-button min-h-11" href="{{ $this->journalData['export_url'] }}">
                <x-ui-icon name="download" />
                {{ __('forum_journals.actions.export') }}
            </a>
        @endif
        @if ($this->journalData['can_archive'])
            <button
                type="button"
                class="forum-button forum-button--danger min-h-11"
                wire:click="archive"
                wire:confirm="{{ __('forum_journals.actions.archive_confirm') }}"
                wire:loading.attr="disabled"
                wire:target="archive"
            >
                <x-ui-icon name="archive" />
                {{ __('forum_journals.actions.archive') }}
            </button>
        @endif
    </div>
</section>
