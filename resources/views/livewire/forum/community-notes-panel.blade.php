<section class="forum-community-notes" aria-labelledby="community-notes-heading">
    <header class="forum-header">
        <div class="forum-header__copy">
            <p class="forum-header__eyebrow">{{ __('forum_review.notes.eyebrow') }}</p>
            <h2 id="community-notes-heading">{{ __('forum_review.notes.heading') }}</h2>
            <p>{{ __('forum_review.notes.description') }}</p>
        </div>
    </header>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <p wire:offline class="border-s-4 border-status-warning py-3 ps-4" role="status">
        {{ __('forum_review.notes.offline') }}
    </p>

    <div class="forum-topic-list">
        @forelse ($this->notes as $note)
            <article class="forum-form" wire:key="community-note-{{ $note['id'] }}">
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge
                        :label="$note['status']"
                        :icon="$note['is_safety_notice'] ? 'shield-alert' : 'notebook-text'"
                    />
                    <strong>{{ $note['type'] }}</strong>
                    <span class="text-sm text-paw-muted">{{ $note['subject_label'] }}</span>
                    <span class="text-sm text-paw-muted">
                        {{ __('forum_review.notes.version', ['version' => $note['version']]) }}
                    </span>
                </div>

                <p class="whitespace-pre-line">{{ $note['body'] }}</p>

                @if ($note['jurisdiction'] || $note['species_context'])
                    <dl class="grid gap-2 text-sm sm:grid-cols-2">
                        @if ($note['jurisdiction'])
                            <div>
                                <dt class="font-semibold">{{ __('forum_review.fields.jurisdiction') }}</dt>
                                <dd>{{ $note['jurisdiction'] }}</dd>
                            </div>
                        @endif
                        @if ($note['species_context'])
                            <div>
                                <dt class="font-semibold">{{ __('forum_review.fields.species_context') }}</dt>
                                <dd>{{ $note['species_context'] }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif

                <div>
                    <h3 class="text-base font-semibold">{{ __('forum_review.notes.evidence') }}</h3>
                    <ul class="mt-2 grid gap-2">
                        @forelse ($note['evidence'] as $evidence)
                            <li>
                                <a href="{{ $evidence['url'] }}" rel="nofollow noopener noreferrer" target="_blank">
                                    <x-lucide-external-link aria-hidden="true" class="icon icon--sm" />
                                    {{ $evidence['label'] }}
                                </a>
                            </li>
                        @empty
                            <li>{{ __('forum_review.notes.no_evidence') }}</li>
                        @endforelse
                    </ul>
                </div>

                @if ($note['author_response'])
                    <aside class="border-s-4 border-paw-line py-2 ps-4">
                        <strong>{{ __('forum_review.notes.author_response') }}</strong>
                        <p class="whitespace-pre-line">{{ $note['author_response'] }}</p>
                    </aside>
                @endif

                @if ($note['revalidation_due'])
                    <p class="text-sm text-paw-muted">
                        {{ __('forum_review.notes.revalidation_due', ['date' => $note['revalidation_due']]) }}
                    </p>
                @endif

                @if ($note['can_respond'])
                    <form wire:submit="respond({{ $note['id'] }})" class="grid gap-3">
                        <label class="forum-form__field">
                            <span>{{ __('forum_review.fields.author_response') }}</span>
                            <textarea
                                wire:model="authorResponse"
                                rows="3"
                                minlength="20"
                                maxlength="2000"
                            ></textarea>
                            @error('authorResponse') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <button
                            type="submit"
                            class="forum-button"
                            wire:loading.attr="disabled"
                            wire:target="respond"
                        >
                            <x-lucide-message-square-reply aria-hidden="true" />
                            {{ __('forum_review.actions.respond') }}
                        </button>
                    </form>
                @endif

                @if ($note['can_moderate'])
                    <form wire:submit="moderate({{ $note['id'] }})" class="grid gap-3 border-t border-paw-line pt-4">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="forum-form__field">
                                <span>{{ __('forum_review.fields.moderation_status') }}</span>
                                <select wire:model="moderationStatus">
                                    @foreach ($this->moderationOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="forum-form__field sm:col-span-2">
                                <span>{{ __('forum_review.fields.moderation_reason') }}</span>
                                <textarea wire:model="moderationReason" rows="3" minlength="20" maxlength="2000"></textarea>
                                @error('moderationReason') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                        </div>
                        <button
                            type="submit"
                            class="forum-button"
                            wire:loading.attr="disabled"
                            wire:target="moderate"
                        >
                            <x-lucide-shield-check aria-hidden="true" />
                            {{ __('forum_review.actions.moderate') }}
                        </button>
                    </form>
                @endif

                @if ($note['can_appeal'] && $note['panel_id'])
                    <details>
                        <summary class="forum-button">{{ __('forum_review.actions.appeal') }}</summary>
                        <form wire:submit="appeal({{ $note['panel_id'] }})" class="mt-3 grid gap-3">
                            <label class="forum-form__field">
                                <span>{{ __('forum_review.fields.appeal_reason') }}</span>
                                <textarea wire:model="appealReason" rows="3" minlength="20" maxlength="2000"></textarea>
                                @error('appealReason') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <button
                                type="submit"
                                class="forum-button"
                                wire:loading.attr="disabled"
                                wire:target="appeal"
                            >
                                <x-lucide-scale aria-hidden="true" />
                                {{ __('forum_review.actions.submit_appeal') }}
                            </button>
                        </form>
                    </details>
                @endif
            </article>
        @empty
            <div class="forum-form">
                <h3>{{ __('forum_review.notes.empty_title') }}</h3>
                <p>{{ __('forum_review.notes.empty_description') }}</p>
            </div>
        @endforelse
    </div>

    @if ($this->assignments !== [])
        <section class="forum-form" aria-labelledby="community-review-assignments-heading">
            <h3 id="community-review-assignments-heading">{{ __('forum_review.assignments.heading') }}</h3>
            <p>{{ __('forum_review.assignments.privacy') }}</p>

            @foreach ($this->assignments as $assignment)
                <article class="border-t border-paw-line pt-4" wire:key="review-assignment-{{ $assignment['id'] }}">
                    <div class="flex flex-wrap items-center gap-2">
                        <strong>{{ $assignment['type'] }}</strong>
                        <span>{{ $assignment['state'] }}</span>
                        <span>{{ __('forum_review.assignments.deadline', ['date' => $assignment['deadline']]) }}</span>
                    </div>
                    <p>{{ $assignment['title'] }}</p>
                    <p class="text-sm text-paw-muted">{{ $assignment['excerpt'] }}</p>
                    <small>{{ __('forum_review.assignments.anonymous_key', ['key' => $assignment['reviewer_key']]) }}</small>

                    <form wire:submit="submitReview({{ $assignment['id'] }})" class="mt-3 grid gap-3">
                        <label class="forum-form__field">
                            <span>{{ __('forum_review.fields.review_decision') }}</span>
                            <select wire:model="reviewDecision">
                                @foreach ($this->decisionOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_review.fields.review_reasoning') }}</span>
                            <textarea wire:model="reviewReasoning" rows="4" minlength="20" maxlength="2000"></textarea>
                            @error('reviewReasoning') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__check">
                            <input type="checkbox" wire:model.live="reviewHasConflict">
                            <span>{{ __('forum_review.fields.has_conflict') }}</span>
                        </label>
                        @if ($reviewHasConflict)
                            <label class="forum-form__field">
                                <span>{{ __('forum_review.fields.conflict_type') }}</span>
                                <input type="text" wire:model="reviewConflictType" maxlength="100">
                                @error('reviewConflictType') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                        @endif
                        <button
                            type="submit"
                            class="forum-button forum-button--primary"
                            wire:loading.attr="disabled"
                            wire:target="submitReview"
                        >
                            <x-lucide-clipboard-check aria-hidden="true" />
                            {{ __('forum_review.actions.submit_review') }}
                        </button>
                    </form>
                </article>
            @endforeach
        </section>
    @endif

    @if ($this->canPropose)
        <form wire:submit="propose" class="forum-form">
            <div>
                <p class="forum-header__eyebrow">{{ __('forum_review.notes.propose_eyebrow') }}</p>
                <h3>{{ __('forum_review.notes.propose_heading') }}</h3>
                <p>{{ __('forum_review.notes.propose_description') }}</p>
            </div>

            @if ($errors->any())
                <p class="form-errors" role="alert">{{ __('forum_review.validation.summary') }}</p>
            @endif

            <label class="forum-form__field">
                <span>{{ __('forum_review.fields.note_type') }}</span>
                <select wire:model="form.noteType">
                    @foreach ($this->noteTypeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="forum-form__field">
                <span>{{ __('forum_review.fields.body') }}</span>
                <textarea wire:model="form.body" rows="5" minlength="40" maxlength="2000"></textarea>
                @error('form.body') <small role="alert">{{ $message }}</small> @enderror
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="forum-form__field">
                    <span>{{ __('forum_review.fields.evidence_url') }}</span>
                    <input type="url" wire:model="form.evidenceUrl" maxlength="500">
                    @error('form.evidenceUrl') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_review.fields.evidence_label') }}</span>
                    <input type="text" wire:model="form.evidenceLabel" maxlength="120">
                    @error('form.evidenceLabel') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_review.fields.jurisdiction') }}</span>
                    <input type="text" wire:model="form.jurisdiction" maxlength="120">
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_review.fields.species_context') }}</span>
                    <input type="text" wire:model="form.speciesContext" maxlength="180">
                </label>
            </div>
            <button
                type="submit"
                class="forum-button forum-button--primary"
                wire:loading.attr="disabled"
                wire:target="propose"
            >
                <x-lucide-notebook-tabs aria-hidden="true" />
                <span wire:loading.remove wire:target="propose">{{ __('forum_review.actions.propose') }}</span>
                <span wire:loading wire:target="propose">{{ __('forum_review.actions.proposing') }}</span>
            </button>
        </form>
    @endif
</section>
