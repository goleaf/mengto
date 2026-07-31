<section class="grid gap-4" aria-labelledby="topic-lifecycle-heading">
    <div wire:offline class="forum-safety" role="status">
        <x-lucide-wifi-off aria-hidden="true" />
        <span>{{ __('forum_topic_lifecycle.panel.offline') }}</span>
    </div>

    @if ($feedback !== '')
        <div class="forum-safety" role="status" aria-live="polite">
            <x-lucide-circle-check aria-hidden="true" />
            <span>{{ $feedback }}</span>
        </div>
    @endif

    @if ($this->lifecycle['is_stale'] || $this->lifecycle['shows_necropost_warning'])
        <aside class="forum-safety" role="note">
            <x-lucide-history aria-hidden="true" />
            <div>
                <strong id="topic-lifecycle-heading">
                    {{ $this->lifecycle['is_stale']
                        ? __('forum_topic_lifecycle.panel.stale_heading')
                        : __('forum_topic_lifecycle.panel.necropost_heading') }}
                </strong>
                <p>
                    {{ $this->lifecycle['is_stale']
                        ? __('forum_topic_lifecycle.panel.stale_description', ['date' => $this->lifecycle['reference_at']])
                        : __('forum_topic_lifecycle.panel.necropost_description', ['date' => $this->lifecycle['reference_at']]) }}
                </p>
            </div>
        </aside>
    @else
        <h2 id="topic-lifecycle-heading" class="sr-only">{{ __('forum_topic_lifecycle.panel.heading') }}</h2>
    @endif

    @if ($errors->any())
        <x-forum-error-summary
            :messages="$errors->getMessages()"
            :heading="__('forum_topic_lifecycle.validation.summary')"
        />
    @endif

    @if ($this->abilities['reopen'] || $this->abilities['archive'] || $this->abilities['remove'] || $this->abilities['bump'])
        <div class="forum-actions" aria-label="{{ __('forum_topic_lifecycle.panel.owner_actions') }}">
            @if (
                $this->abilities['reopen']
                && in_array($this->lifecycle['status'], ['answered', 'partially-solved', 'solved', 'disputed', 'outdated', 'locked', 'restored'], true)
            )
                <button
                    type="button"
                    class="forum-button min-h-11"
                    wire:click="changeState('open')"
                    wire:loading.attr="disabled"
                    wire:target="changeState"
                >
                    <x-lucide-rotate-ccw aria-hidden="true" />
                    {{ __('forum_topic_lifecycle.actions.reopen') }}
                </button>
            @endif

            @if (
                $this->abilities['reopen']
                && in_array($this->lifecycle['status'], ['published', 'open', 'answered', 'partially-solved', 'disputed', 'outdated', 'restored'], true)
            )
                <button
                    type="button"
                    class="forum-button min-h-11"
                    wire:click="changeState('solved')"
                    wire:loading.attr="disabled"
                    wire:target="changeState"
                >
                    <x-lucide-circle-check-big aria-hidden="true" />
                    {{ __('forum_topic_lifecycle.actions.mark_solved') }}
                </button>
            @endif

            @if ($this->abilities['bump'] && $this->lifecycle['can_bump_now'])
                <button
                    type="button"
                    class="forum-button min-h-11"
                    wire:click="bump"
                    wire:loading.attr="disabled"
                    wire:target="bump"
                >
                    <x-lucide-arrow-up aria-hidden="true" />
                    {{ __('forum_topic_lifecycle.actions.bump') }}
                </button>
            @elseif ($this->abilities['bump'] && $this->lifecycle['next_bump_at'])
                <span class="text-sm text-paw-muted">
                    {{ __('forum_topic_lifecycle.panel.bump_available', ['date' => $this->lifecycle['next_bump_at']]) }}
                </span>
            @endif

            @if ($this->abilities['archive'] && $this->lifecycle['status'] !== 'archived')
                <button
                    type="button"
                    class="forum-button min-h-11"
                    wire:click="changeState('archived')"
                    wire:loading.attr="disabled"
                    wire:target="changeState"
                    wire:confirm="{{ __('forum_topic_lifecycle.confirm.archive') }}"
                >
                    <x-lucide-archive aria-hidden="true" />
                    {{ __('forum_topic_lifecycle.actions.archive') }}
                </button>
            @endif

            @if ($this->abilities['restore'] && in_array($this->lifecycle['status'], ['archived', 'removed'], true))
                <button
                    type="button"
                    class="forum-button min-h-11"
                    wire:click="changeState('restored')"
                    wire:loading.attr="disabled"
                    wire:target="changeState"
                >
                    <x-lucide-archive-restore aria-hidden="true" />
                    {{ __('forum_topic_lifecycle.actions.restore') }}
                </button>
            @endif

            @if ($this->abilities['remove'] && $this->lifecycle['status'] !== 'removed')
                <button
                    type="button"
                    class="forum-button forum-button--danger min-h-11"
                    wire:click="changeState('removed')"
                    wire:loading.attr="disabled"
                    wire:target="changeState"
                    wire:confirm="{{ __('forum_topic_lifecycle.confirm.remove') }}"
                >
                    <x-lucide-trash-2 aria-hidden="true" />
                    {{ __('forum_topic_lifecycle.actions.remove') }}
                </button>
            @endif
        </div>
    @endif

    @if ($this->abilities['request_update'])
        <details class="forum-form">
            <summary class="forum-button min-h-11">
                <x-lucide-file-pen-line aria-hidden="true" />
                {{ __('forum_topic_lifecycle.actions.request_update') }}
            </summary>
            <form wire:submit="requestUpdate" class="mt-4 grid gap-4">
                <label class="forum-form__field">
                    <span>{{ __('forum_topic_lifecycle.fields.request_kind') }}</span>
                    <select wire:model.live="form.requestKind">
                        @forelse ($this->requestKindOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_topic_lifecycle.fields.request_reason') }}</span>
                    <textarea wire:model="form.requestReason" rows="4" minlength="20" maxlength="2000" required></textarea>
                    @error('form.requestReason') <small role="alert">{{ $message }}</small> @enderror
                </label>
                @if ($form->requestKind === 'community-proposal')
                    <label class="forum-form__field">
                        <span>{{ __('forum_topic_lifecycle.fields.proposed_body') }}</span>
                        <textarea wire:model="form.proposedBody" rows="8" minlength="40" maxlength="10000" required></textarea>
                        @error('form.proposedBody') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                @endif
                <button
                    type="submit"
                    class="forum-button forum-button--primary min-h-11 justify-self-start"
                    wire:loading.attr="disabled"
                    wire:target="requestUpdate"
                >
                    <x-lucide-send aria-hidden="true" />
                    <span wire:loading.remove wire:target="requestUpdate">{{ __('forum_topic_lifecycle.actions.submit_request') }}</span>
                    <span wire:loading wire:target="requestUpdate">{{ __('forum_topic_lifecycle.actions.submitting') }}</span>
                </button>
            </form>
        </details>
    @endif

    @if ($this->updateRequests !== [])
        <details class="forum-form">
            <summary class="forum-button min-h-11">
                <x-lucide-list-checks aria-hidden="true" />
                {{ __('forum_topic_lifecycle.panel.update_requests') }}
            </summary>
            <div class="mt-4 grid gap-4">
                @forelse ($this->updateRequests as $request)
                    <article class="border-t border-paw-line pt-4" wire:key="topic-update-request-{{ $request['id'] }}">
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <strong>{{ $request['kind'] }}</strong>
                            <span>{{ $request['status'] }}</span>
                            <span>{{ $request['created'] }}</span>
                        </div>
                        <p class="whitespace-pre-line">{{ $request['reason'] }}</p>
                        @if ($request['proposed_body'])
                            <div>
                                <strong>{{ __('forum_topic_lifecycle.fields.proposed_body') }}</strong>
                                <p class="whitespace-pre-line">{{ $request['proposed_body'] }}</p>
                            </div>
                        @endif
                        @if ($request['resolution_reason'])
                            <p>{{ __('forum_topic_lifecycle.panel.resolution', ['reason' => $request['resolution_reason']]) }}</p>
                        @endif

                        @if ($this->abilities['review_requests'] && $request['status_value'] === 'pending')
                            <form wire:submit="reviewRequest({{ $request['id'] }}, {{ $request['lock_version'] }})" class="mt-3 grid gap-3">
                                <label class="forum-form__field">
                                    <span>{{ __('forum_topic_lifecycle.fields.review_decision') }}</span>
                                    <select wire:model="form.reviewDecision">
                                        @forelse ($this->reviewDecisionOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </label>
                                <label class="forum-form__field">
                                    <span>{{ __('forum_topic_lifecycle.fields.review_reason') }}</span>
                                    <textarea wire:model="form.reviewReason" rows="3" minlength="10" maxlength="2000" required></textarea>
                                    @error('form.reviewReason') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                                <button type="submit" class="forum-button min-h-11 justify-self-start" wire:loading.attr="disabled" wire:target="reviewRequest">
                                    <x-lucide-clipboard-check aria-hidden="true" />
                                    {{ __('forum_topic_lifecycle.actions.review_request') }}
                                </button>
                            </form>
                        @endif
                    </article>
                @empty
                @endforelse
            </div>
        </details>
    @endif

    @if ($this->history !== [])
        <details class="forum-form">
            <summary class="forum-button min-h-11">
                <x-lucide-history aria-hidden="true" />
                {{ __('forum_topic_lifecycle.panel.history') }}
            </summary>
            <ol class="mt-4 grid gap-3">
                @forelse ($this->history as $event)
                    <li class="border-t border-paw-line pt-3" wire:key="topic-lifecycle-event-{{ $event['id'] }}">
                        <strong>{{ $event['event'] }}</strong>
                        @if ($event['from'] && $event['to'])
                            <span>{{ __('forum_topic_lifecycle.panel.transition', ['from' => $event['from'], 'to' => $event['to']]) }}</span>
                        @endif
                        @if ($event['reason'])
                            <span>{{ $event['reason'] }}</span>
                        @endif
                        <time>{{ $event['occurred'] }}</time>
                    </li>
                @empty
                @endforelse
            </ol>
        </details>
    @endif

    @if ($this->abilities['moderate'])
        <details class="forum-form">
            <summary class="forum-button min-h-11">
                <x-lucide-shield-check aria-hidden="true" />
                {{ __('forum_topic_lifecycle.panel.moderation') }}
            </summary>
            <div class="mt-4 grid gap-4">
                <label class="forum-form__field">
                    <span>{{ __('forum_topic_lifecycle.fields.moderation_status') }}</span>
                    <select wire:model="moderationStatus">
                        @forelse ($this->moderationStatusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                        @endforelse
                    </select>
                </label>
                <button type="button" class="forum-button min-h-11 justify-self-start" wire:click="moderateState" wire:loading.attr="disabled" wire:target="moderateState">
                    <x-lucide-refresh-cw aria-hidden="true" />
                    {{ __('forum_topic_lifecycle.actions.change_state') }}
                </button>

                @if ($this->abilities['redirect'])
                    <form wire:submit="redirectTopic('redirected')" class="grid gap-3">
                        <label class="forum-form__field">
                            <span>{{ __('forum_topic_lifecycle.fields.redirect_slug') }}</span>
                            <input wire:model="form.redirectSlug" type="text" maxlength="180" required>
                            @error('form.redirectSlug') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <div class="forum-actions">
                            <button type="submit" class="forum-button min-h-11" wire:loading.attr="disabled" wire:target="redirectTopic">
                                <x-lucide-corner-up-right aria-hidden="true" />
                                {{ __('forum_topic_lifecycle.actions.redirect') }}
                            </button>
                            <button type="button" class="forum-button min-h-11" wire:click="redirectTopic('merged')" wire:loading.attr="disabled" wire:target="redirectTopic">
                                <x-lucide-merge aria-hidden="true" />
                                {{ __('forum_topic_lifecycle.actions.merge') }}
                            </button>
                        </div>
                    </form>
                @endif

                @if ($this->abilities['legal_hold'])
                    @if (! $this->lifecycle['has_legal_hold'])
                        <form wire:submit="applyLegalHold" class="grid gap-3">
                            <label class="forum-form__field">
                                <span>{{ __('forum_topic_lifecycle.fields.hold_reason_code') }}</span>
                                <input wire:model="form.legalHoldReasonCode" type="text" maxlength="100" required>
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_topic_lifecycle.fields.hold_private_reason') }}</span>
                                <textarea wire:model="form.legalHoldPrivateReason" rows="4" minlength="20" maxlength="5000" required></textarea>
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_topic_lifecycle.fields.hold_review_at') }}</span>
                                <input wire:model="form.legalHoldReviewAt" type="datetime-local">
                            </label>
                            <button type="submit" class="forum-button min-h-11 justify-self-start" wire:loading.attr="disabled" wire:target="applyLegalHold">
                                <x-lucide-lock-keyhole aria-hidden="true" />
                                {{ __('forum_topic_lifecycle.actions.apply_hold') }}
                            </button>
                        </form>
                    @else
                        <form wire:submit="releaseLegalHold" class="grid gap-3">
                            <label class="forum-form__field">
                                <span>{{ __('forum_topic_lifecycle.fields.hold_release_reason') }}</span>
                                <textarea wire:model="form.legalHoldReleaseReason" rows="4" minlength="20" maxlength="5000" required></textarea>
                            </label>
                            <button type="submit" class="forum-button min-h-11 justify-self-start" wire:loading.attr="disabled" wire:target="releaseLegalHold">
                                <x-lucide-lock-keyhole-open aria-hidden="true" />
                                {{ __('forum_topic_lifecycle.actions.release_hold') }}
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </details>
    @endif

    <span class="sr-only" aria-live="polite" wire:loading>{{ __('forum_topic_lifecycle.panel.processing') }}</span>
</section>
