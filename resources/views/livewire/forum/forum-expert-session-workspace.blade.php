<article class="grid gap-6" aria-labelledby="expert-session-heading">
    <x-page-header
        :eyebrow="__('forum_expert_sessions.page.session_eyebrow')"
        :title="$this->session['title']"
        :description="$this->session['summary']"
        heading-id="expert-session-heading"
        data-section="forum-expert-session-workspace-header"
    >
        <x-slot:meta>
            <x-status-badge :label="$this->session['phase']" icon="messages-square" />
        </x-slot:meta>
    </x-page-header>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <p class="hidden border-s-4 border-status-warning py-3 ps-4" wire:offline.class.remove="hidden" role="status">
        {{ __('forum_expert_sessions.notices.offline') }}
    </p>

    <section class="forum-form" aria-labelledby="expert-session-details-heading">
        <h2 id="expert-session-details-heading">{{ __('forum_expert_sessions.detail.heading') }}</h2>
        <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="font-semibold">{{ __('forum_expert_sessions.fields.host') }}</dt>
                <dd><a class="inline-flex min-h-11 items-center" href="{{ $this->session['host_url'] }}">{{ $this->session['host_name'] }}</a></dd>
            </div>
            <div>
                <dt class="font-semibold">{{ __('forum_expert_sessions.fields.professional_scope') }}</dt>
                <dd>{{ $this->session['scope'] }}</dd>
            </div>
            <div>
                <dt class="font-semibold">{{ __('forum_expert_sessions.fields.jurisdiction') }}</dt>
                <dd>{{ $this->session['jurisdiction'] }}</dd>
            </div>
            <div>
                <dt class="font-semibold">{{ __('forum_expert_sessions.fields.question_window') }}</dt>
                <dd>{{ __('forum_expert_sessions.labels.date_range', ['start' => $this->session['question_opens_at'], 'end' => $this->session['question_closes_at']]) }}</dd>
            </div>
            <div>
                <dt class="font-semibold">{{ __('forum_expert_sessions.fields.session_window') }}</dt>
                <dd>{{ __('forum_expert_sessions.labels.date_range', ['start' => $this->session['starts_at'], 'end' => $this->session['ends_at']]) }}</dd>
            </div>
            <div>
                <dt class="font-semibold">{{ __('forum_expert_sessions.fields.timezone') }}</dt>
                <dd>{{ $this->session['timezone'] }}</dd>
            </div>
            <div>
                <dt class="font-semibold">{{ __('forum_expert_sessions.fields.status') }}</dt>
                <dd>{{ $this->session['status'] }}</dd>
            </div>
        </dl>
    </section>

    <aside class="border-s-4 border-status-warning py-3 ps-4" aria-labelledby="expert-session-disclaimer-heading">
        <h2 id="expert-session-disclaimer-heading" class="text-base">{{ __('forum_expert_sessions.disclaimers.heading') }}</h2>
        <p>{{ $this->session['disclaimer'] }}</p>
    </aside>

    @if ($this->session['can_ask'])
        <section class="forum-form" aria-labelledby="expert-session-question-form-heading">
            <h2 id="expert-session-question-form-heading">{{ __('forum_expert_sessions.question_form.heading') }}</h2>
            <form wire:submit="submitQuestion" class="mt-4 grid gap-4">
                <label class="forum-form__field">
                    <span>{{ __('forum_expert_sessions.fields.question') }}</span>
                    <textarea wire:model="questionForm.body" rows="5" minlength="10" maxlength="4000" required></textarea>
                    @error('questionForm.body') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <button class="forum-button forum-button--primary min-h-11 justify-self-start" type="submit" wire:loading.attr="disabled" wire:target="submitQuestion">
                    <x-ui-icon name="send" />
                    <span wire:loading.remove wire:target="submitQuestion">{{ __('forum_expert_sessions.actions.submit_question') }}</span>
                    <span wire:loading wire:target="submitQuestion">{{ __('forum_expert_sessions.actions.submitting') }}</span>
                </button>
            </form>
        </section>
    @endif

    <section aria-labelledby="expert-session-queue-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="expert-session-queue-heading">{{ __('forum_expert_sessions.queue.heading') }}</h2>
                <p>{{ __('forum_expert_sessions.queue.description') }}</p>
            </div>
            <span wire:loading wire:target="withdrawQuestion,moderate,answer,correct" role="status">
                {{ __('forum_expert_sessions.actions.updating') }}
            </span>
        </div>

        <ol class="mt-4 grid gap-4">
            @forelse ($this->questions as $question)
                <li class="forum-form" wire:key="expert-question-{{ $question['id'] }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="font-semibold">{{ __('forum_expert_sessions.labels.queue_position', ['position' => $question['queue_position']]) }}</span>
                        <x-status-badge :label="$question['status']" icon="circle-help" />
                    </div>
                    <blockquote>{{ $question['body'] }}</blockquote>
                    <p class="text-sm">{{ __('forum_expert_sessions.labels.asked_by', ['name' => $question['author_name']]) }}</p>

                    @if ($question['is_unanswered'])
                        <p class="text-sm">{{ __('forum_expert_sessions.labels.unanswered') }}</p>
                    @endif
                    @if ($question['moderation_reason'])
                        <p class="border-s-4 border-status-warning py-2 ps-3">{{ $question['moderation_reason'] }}</p>
                    @endif

                    <div class="flex flex-wrap gap-2">
                        @if ($question['can_withdraw'])
                            <button class="forum-button min-h-11" type="button" wire:click="withdrawQuestion({{ $question['id'] }})" wire:loading.attr="disabled" wire:target="withdrawQuestion">
                                <x-ui-icon name="undo-2" />
                                {{ __('forum_expert_sessions.actions.withdraw_question') }}
                            </button>
                        @endif
                        @if ($question['can_moderate'])
                            <button class="forum-button min-h-11" type="button" wire:click="prepareModeration({{ $question['id'] }})">
                                <x-ui-icon name="shield-check" />
                                {{ __('forum_expert_sessions.actions.moderate') }}
                            </button>
                        @endif
                        @if ($question['can_answer'])
                            <button class="forum-button forum-button--primary min-h-11" type="button" wire:click="prepareAnswer({{ $question['id'] }})">
                                <x-ui-icon name="message-square-reply" />
                                {{ __('forum_expert_sessions.actions.answer') }}
                            </button>
                        @endif
                        @if ($question['can_report'])
                            <button class="forum-button min-h-11" type="button" wire:click="prepareReport('question', {{ $question['id'] }})">
                                <x-ui-icon name="flag" />
                                {{ __('forum_expert_sessions.actions.report_question') }}
                            </button>
                        @endif
                    </div>

                    @if ($moderationQuestionId === $question['id'])
                        <form wire:submit="moderate" class="grid gap-4 border-s-4 border-border-strong py-3 ps-4">
                            <h3>{{ __('forum_expert_sessions.moderation.heading') }}</h3>
                            <label class="forum-form__field">
                                <span>{{ __('forum_expert_sessions.fields.moderation_decision') }}</span>
                                <select wire:model.live="moderationForm.decision">
                                    @forelse ($this->moderationDecisionOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </label>
                            @if (in_array($moderationForm->decision, ['decline', 'remove'], true))
                                <label class="forum-form__field">
                                    <span>{{ __('forum_expert_sessions.fields.moderation_reason') }}</span>
                                    <textarea wire:model="moderationForm.reason" rows="3" maxlength="1000" required></textarea>
                                    @error('moderationForm.reason') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                            @endif
                            <button class="forum-button forum-button--primary min-h-11 justify-self-start" type="submit">
                                <x-ui-icon name="check" />
                                {{ __('forum_expert_sessions.actions.apply_decision') }}
                            </button>
                        </form>
                    @endif

                    @if ($answerQuestionId === $question['id'])
                        <form wire:submit="answer" class="grid gap-4 border-s-4 border-status-info py-3 ps-4">
                            <h3>{{ __('forum_expert_sessions.answer_form.heading') }}</h3>
                            <label class="forum-form__field">
                                <span>{{ __('forum_expert_sessions.fields.answer') }}</span>
                                <textarea wire:model="answerForm.body" rows="7" minlength="20" maxlength="20000" required></textarea>
                                @error('answerForm.body') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="forum-form__field">
                                <span>{{ __('forum_expert_sessions.fields.source_urls') }}</span>
                                <textarea wire:model="answerForm.sourceUrls" rows="3" maxlength="10000" placeholder="{{ __('forum_expert_sessions.answer_form.sources_placeholder') }}"></textarea>
                                @error('answerForm.sourceUrls') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <button class="forum-button forum-button--primary min-h-11 justify-self-start" type="submit">
                                <x-ui-icon name="send" />
                                {{ __('forum_expert_sessions.actions.publish_answer') }}
                            </button>
                        </form>
                    @endif

                    @if ($question['answer'])
                        <article class="border-s-4 border-status-success py-3 ps-4" aria-labelledby="expert-answer-{{ $question['answer']['id'] }}">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 id="expert-answer-{{ $question['answer']['id'] }}">{{ __('forum_expert_sessions.answer.heading') }}</h3>
                                <span>{{ $question['answer']['status'] }}</span>
                            </div>
                            <p>{{ $question['answer']['body'] }}</p>
                            <p class="text-sm">{{ __('forum_expert_sessions.labels.answered_by', ['name' => $question['answer']['author_name'], 'date' => $question['answer']['answered_at']]) }}</p>

                            @if ($question['answer']['source_links'] !== [])
                                <h4>{{ __('forum_expert_sessions.answer.sources') }}</h4>
                                <ul class="list-disc ps-5">
                                    @forelse ($question['answer']['source_links'] as $source)
                                        <li><a class="inline-flex min-h-11 items-center" href="{{ $source['url'] }}" rel="nofollow noopener noreferrer" target="_blank">{{ $source['label'] }}</a></li>
                                    @empty
                                    @endforelse
                                </ul>
                            @endif

                            @if ($question['answer']['corrections'] !== [])
                                <h4>{{ __('forum_expert_sessions.answer.corrections') }}</h4>
                                <ul class="grid gap-1 text-sm">
                                    @forelse ($question['answer']['corrections'] as $correction)
                                        <li>{{ __('forum_expert_sessions.labels.correction', ['version' => $correction['version'], 'reason' => $correction['reason'], 'date' => $correction['created_at']]) }}</li>
                                    @empty
                                    @endforelse
                                </ul>
                            @endif

                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($question['answer']['can_correct'])
                                    <button class="forum-button min-h-11" type="button" wire:click="prepareCorrection({{ $question['answer']['id'] }})">
                                        <x-ui-icon name="file-pen-line" />
                                        {{ __('forum_expert_sessions.actions.correct_answer') }}
                                    </button>
                                @endif
                                @if ($question['answer']['can_report'])
                                    <button class="forum-button min-h-11" type="button" wire:click="prepareReport('answer', {{ $question['answer']['id'] }})">
                                        <x-ui-icon name="flag" />
                                        {{ __('forum_expert_sessions.actions.report_answer') }}
                                    </button>
                                @endif
                            </div>

                            @if ($correctionAnswerId === $question['answer']['id'])
                                <form wire:submit="correct" class="mt-4 grid gap-4">
                                    <h4>{{ __('forum_expert_sessions.correction_form.heading') }}</h4>
                                    <label class="forum-form__field">
                                        <span>{{ __('forum_expert_sessions.fields.corrected_answer') }}</span>
                                        <textarea wire:model="correctionForm.body" rows="7" minlength="20" maxlength="20000" required></textarea>
                                    </label>
                                    <label class="forum-form__field">
                                        <span>{{ __('forum_expert_sessions.fields.source_urls') }}</span>
                                        <textarea wire:model="correctionForm.sourceUrls" rows="3" maxlength="10000"></textarea>
                                    </label>
                                    <label class="forum-form__field">
                                        <span>{{ __('forum_expert_sessions.fields.correction_reason') }}</span>
                                        <textarea wire:model="correctionForm.reason" rows="3" minlength="5" maxlength="1000" required></textarea>
                                    </label>
                                    <button class="forum-button forum-button--primary min-h-11 justify-self-start" type="submit">
                                        <x-ui-icon name="save" />
                                        {{ __('forum_expert_sessions.actions.save_correction') }}
                                    </button>
                                </form>
                            @endif
                        </article>
                    @endif
                </li>
            @empty
                <li class="forum-form">
                    <h3>{{ __('forum_expert_sessions.empty.questions_title') }}</h3>
                    <p>{{ __('forum_expert_sessions.empty.questions_description') }}</p>
                </li>
            @endforelse
        </ol>
    </section>

    @if ($this->session['can_report'])
        <section class="forum-form" aria-labelledby="expert-session-report-heading">
            <h2 id="expert-session-report-heading">{{ __('forum_expert_sessions.report.heading') }}</h2>
            <p>{{ __('forum_expert_sessions.report.selected_subject', ['subject' => __('forum_expert_sessions.report_subjects.'.$reportSubjectType)]) }}</p>
            <button class="forum-button min-h-11 justify-self-start" type="button" wire:click="prepareReport('session', null)">
                <x-ui-icon name="flag" />
                {{ __('forum_expert_sessions.actions.report_session') }}
            </button>
            <form wire:submit="report" class="mt-4 grid gap-4">
                <label class="forum-form__field">
                    <span>{{ __('forum_expert_sessions.fields.report_reason') }}</span>
                    <select wire:model="reportForm.reason" required>
                        @forelse ($this->reportReasonOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_expert_sessions.fields.report_description') }}</span>
                    <textarea wire:model="reportForm.description" rows="4" maxlength="1200"></textarea>
                </label>
                <label class="inline-flex min-h-11 items-center gap-3">
                    <input type="checkbox" wire:model="reportForm.immediateSafety">
                    <span>{{ __('forum_expert_sessions.fields.immediate_safety') }}</span>
                </label>
                <label class="inline-flex min-h-11 items-center gap-3">
                    <input type="checkbox" wire:model="reportForm.truthfulnessConfirmed" required>
                    <span>{{ __('forum_expert_sessions.fields.truthfulness_confirmed') }}</span>
                </label>
                <button class="forum-button min-h-11 justify-self-start" type="submit" wire:loading.attr="disabled" wire:target="report">
                    <x-ui-icon name="flag" />
                    {{ __('forum_expert_sessions.actions.submit_report') }}
                </button>
            </form>
        </section>
    @endif

    @if ($this->session['can_archive'])
        <section class="forum-form" aria-labelledby="expert-session-archive-heading">
            <h2 id="expert-session-archive-heading">{{ __('forum_expert_sessions.archive.heading') }}</h2>
            <p>{{ __('forum_expert_sessions.archive.description') }}</p>
            <button class="forum-button min-h-11 justify-self-start" type="button" wire:click="archive" wire:confirm="{{ __('forum_expert_sessions.archive.confirm') }}" wire:loading.attr="disabled" wire:target="archive">
                <x-ui-icon name="archive" />
                {{ __('forum_expert_sessions.actions.archive') }}
            </button>
        </section>
    @endif
</article>
