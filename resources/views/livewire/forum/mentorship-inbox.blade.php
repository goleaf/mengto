<section class="grid gap-5" aria-labelledby="mentorship-inbox-heading">
    <header>
        <h2 id="mentorship-inbox-heading">{{ __('forum_mentorship.inbox.heading') }}</h2>
        <p>{{ __('forum_mentorship.inbox.description') }}</p>
    </header>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <p wire:offline class="border-s-4 border-status-warning py-3 ps-4" role="status">
        {{ __('forum_mentorship.inbox.offline') }}
    </p>

    <div class="grid gap-4">
        @forelse ($this->mentorships as $mentorship)
            <article class="forum-form" wire:key="mentorship-{{ $mentorship['id'] }}">
                <header class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3>{{ $mentorship['type'] }}</h3>
                        <p>{{ __('forum_mentorship.inbox.mentor', ['name' => $mentorship['mentor_name']]) }}</p>
                        <p>{{ __('forum_mentorship.inbox.mentee', ['name' => $mentorship['mentee_name']]) }}</p>
                        <small>{{ __('forum_mentorship.inbox.requested_at', ['date' => $mentorship['requested_at']]) }}</small>
                    </div>
                    <x-status-badge :label="$mentorship['state']" icon="users-round" />
                </header>

                <blockquote class="border-s-4 border-paw-line py-2 ps-4">
                    {{ $mentorship['request_message'] }}
                </blockquote>

                @if ($mentorship['mentor_response'])
                    <p>{{ $mentorship['mentor_response'] }}</p>
                @endif

                @if ($mentorship['can_respond'])
                    <form wire:submit="respond({{ $mentorship['id'] }}, true, {{ $mentorship['lock_version'] }})" class="grid gap-3">
                        <label class="forum-form__field">
                            <span>{{ __('forum_mentorship.fields.mentor_response') }}</span>
                            <textarea wire:model="mentorResponse" rows="3" minlength="2" maxlength="2000"></textarea>
                            @error('mentorResponse') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__check">
                            <input type="checkbox" wire:model="responseSafetyAcknowledged">
                            <span>{{ __('forum_mentorship.fields.safety_acknowledgement') }}</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="submit"
                                class="forum-button forum-button--primary min-h-11"
                                wire:loading.attr="disabled"
                                wire:target="respond"
                            >
                                <x-lucide-check aria-hidden="true" />
                                {{ __('forum_mentorship.inbox.accept') }}
                            </button>
                            <button
                                type="button"
                                class="forum-button min-h-11"
                                wire:click="respond({{ $mentorship['id'] }}, false, {{ $mentorship['lock_version'] }})"
                                wire:loading.attr="disabled"
                                wire:target="respond"
                            >
                                <x-lucide-x aria-hidden="true" />
                                {{ __('forum_mentorship.inbox.decline') }}
                            </button>
                        </div>
                    </form>
                @endif

                @if ($mentorship['can_message'] || $mentorship['messages'] !== [])
                    <section aria-labelledby="mentorship-thread-{{ $mentorship['id'] }}">
                        <h4 id="mentorship-thread-{{ $mentorship['id'] }}">
                            {{ __('forum_mentorship.inbox.thread') }}
                        </h4>
                        <div class="mt-3 grid max-h-96 gap-2 overflow-y-auto" aria-live="polite">
                            @forelse ($mentorship['messages'] as $message)
                                <div
                                    class="max-w-[90%] rounded-md border border-paw-line px-3 py-2 {{ $message['is_own'] ? 'ms-auto bg-paw-mint' : 'bg-white' }}"
                                    wire:key="mentorship-message-{{ $message['id'] }}"
                                >
                                    <strong class="block text-sm">{{ $message['sender'] }}</strong>
                                    <p class="whitespace-pre-wrap break-words">{{ $message['body'] }}</p>
                                    <small>{{ $message['created_at'] }}</small>
                                </div>
                            @empty
                                <p>{{ __('forum_mentorship.inbox.no_messages') }}</p>
                            @endforelse
                        </div>

                        @if ($mentorship['can_message'])
                            <form wire:submit="sendMessage({{ $mentorship['id'] }})" class="mt-3 grid gap-3">
                                <label class="forum-form__field">
                                    <span>{{ __('forum_mentorship.fields.message') }}</span>
                                    <textarea wire:model="messageBody" rows="3" minlength="2" maxlength="4000"></textarea>
                                    @error('messageBody') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                                <button
                                    type="submit"
                                    class="forum-button forum-button--primary min-h-11"
                                    wire:loading.attr="disabled"
                                    wire:target="sendMessage"
                                >
                                    <x-lucide-send aria-hidden="true" />
                                    <span wire:loading.remove wire:target="sendMessage">{{ __('forum_mentorship.inbox.send_message') }}</span>
                                    <span wire:loading wire:target="sendMessage">{{ __('forum_mentorship.inbox.sending_message') }}</span>
                                </button>
                            </form>
                        @endif
                    </section>
                @endif

                @if ($mentorship['can_end'])
                    <details>
                        <summary class="forum-button min-h-11">
                            <x-lucide-circle-stop aria-hidden="true" />
                            {{ __('forum_mentorship.inbox.end_heading') }}
                        </summary>
                        <form wire:submit="end({{ $mentorship['id'] }}, {{ $mentorship['lock_version'] }})" class="mt-3 grid gap-3">
                            <label class="forum-form__field">
                                <span>{{ __('forum_mentorship.fields.end_reason') }}</span>
                                <textarea wire:model="endReason" rows="3" minlength="2" maxlength="2000"></textarea>
                            </label>
                            @if ($mentorship['state_key'] === 'active')
                                <label class="forum-form__check">
                                    <input type="checkbox" wire:model="markCompleted">
                                    <span>{{ __('forum_mentorship.fields.mark_completed') }}</span>
                                </label>
                            @endif
                            <label class="forum-form__check">
                                <input type="checkbox" wire:model="blockCounterpart">
                                <span>{{ __('forum_mentorship.fields.block_counterpart') }}</span>
                            </label>
                            <button
                                type="submit"
                                class="forum-button min-h-11"
                                wire:loading.attr="disabled"
                                wire:target="end"
                            >
                                <x-lucide-circle-stop aria-hidden="true" />
                                {{ __('forum_mentorship.inbox.end') }}
                            </button>
                        </form>
                    </details>
                @endif

                @if ($mentorship['state_key'] === 'completed')
                    <p class="text-sm">
                        {{ $mentorship['completion_validated']
                            ? __('forum_mentorship.inbox.validated')
                            : __('forum_mentorship.inbox.pending_validation') }}
                    </p>
                @endif

                @if ($mentorship['can_validate'])
                    <button
                        type="button"
                        class="forum-button forum-button--primary min-h-11"
                        wire:click="validateCompletion({{ $mentorship['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="validateCompletion"
                    >
                        <x-lucide-badge-check aria-hidden="true" />
                        {{ __('forum_mentorship.inbox.validate') }}
                    </button>
                @endif

                @if ($mentorship['can_feedback'])
                    <details>
                        <summary class="forum-button min-h-11">
                            <x-lucide-message-square-heart aria-hidden="true" />
                            {{ __('forum_mentorship.inbox.feedback_heading') }}
                        </summary>
                        <form wire:submit="submitFeedback({{ $mentorship['id'] }})" class="mt-3 grid gap-3">
                            <label class="forum-form__field">
                                <span>{{ __('forum_mentorship.fields.rating') }}</span>
                                <input type="number" wire:model="feedbackRating" min="1" max="5">
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_mentorship.fields.feedback_summary') }}</span>
                                <textarea wire:model="feedbackSummary" rows="3" minlength="2" maxlength="1000"></textarea>
                            </label>
                            <label class="forum-form__check">
                                <input type="checkbox" wire:model="wouldRecommend">
                                <span>{{ __('forum_mentorship.fields.recommend') }}</span>
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_mentorship.fields.private_note') }}</span>
                                <textarea wire:model="privateFeedbackNote" rows="2" maxlength="2000"></textarea>
                            </label>
                            <button type="submit" class="forum-button forum-button--primary min-h-11">
                                <x-lucide-send aria-hidden="true" />
                                {{ __('forum_mentorship.inbox.submit_feedback') }}
                            </button>
                        </form>
                    </details>
                @endif

                @if ($mentorship['can_report'])
                    <details>
                        <summary class="forum-button min-h-11">
                            <x-lucide-shield-alert aria-hidden="true" />
                            {{ __('forum_mentorship.inbox.report_heading') }}
                        </summary>
                        <form wire:submit="report({{ $mentorship['id'] }})" class="mt-3 grid gap-3">
                            <label class="forum-form__field">
                                <span>{{ __('forum_mentorship.fields.report_reason') }}</span>
                                <select wire:model="reportReason">
                                    @forelse ($this->reportReasonOptions as $reason)
                                        <option value="{{ $reason['key'] }}">{{ $reason['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('forum_mentorship.fields.no_report_reasons') }}</option>
                                    @endforelse
                                </select>
                                @error('reportReason') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_mentorship.fields.report_details') }}</span>
                                <textarea wire:model="reportDetails" rows="3" minlength="10" maxlength="3000"></textarea>
                                @error('reportDetails') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__check">
                                <input type="checkbox" wire:model="reportImmediateSafety">
                                <span>{{ __('forum_mentorship.fields.immediate_safety') }}</span>
                            </label>
                            <label class="forum-form__check">
                                <input type="checkbox" wire:model="reportAndBlock">
                                <span>{{ __('forum_mentorship.fields.report_and_block') }}</span>
                            </label>
                            <label class="forum-form__check">
                                <input type="checkbox" wire:model="reportTruthfulnessConfirmed" required>
                                <span>{{ __('forum_mentorship.fields.truthfulness_confirmation') }}</span>
                                @error('reportTruthfulnessConfirmed') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <button
                                type="submit"
                                class="forum-button min-h-11"
                                wire:loading.attr="disabled"
                                wire:target="report"
                            >
                                <x-lucide-flag aria-hidden="true" />
                                {{ __('forum_mentorship.inbox.report') }}
                            </button>
                        </form>
                    </details>
                @endif
            </article>
        @empty
            <div class="forum-form">
                <h3>{{ __('forum_mentorship.inbox.empty_title') }}</h3>
                <p>{{ __('forum_mentorship.inbox.empty_description') }}</p>
            </div>
        @endforelse
    </div>
</section>
