<div class="mt-5 space-y-8">
    @if (session('feedback'))
        <p role="status" aria-live="polite" class="rounded-md border border-status-success bg-paw-paper px-4 py-3">
            {{ session('feedback') }}
        </p>
    @endif

    <section aria-labelledby="unassigned-reports-heading">
        <div class="mb-3">
            <h2 id="unassigned-reports-heading" class="text-xl font-semibold">
                {{ __('forum_admin.moderation_operations.reports.heading') }}
            </h2>
            <p class="mt-1 text-sm text-paw-muted">
                {{ __('forum_admin.moderation_operations.reports.summary') }}
            </p>
        </div>

        <div class="relative overflow-x-auto border-y border-paw-line bg-white">
            <table class="w-full min-w-[54rem] text-sm">
                <caption class="sr-only">{{ __('forum_accessibility.tables.reports') }}</caption>
                <thead class="bg-paw-paper">
                    <tr>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.subject') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.reason') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.priority') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.status') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.created') }}</th>
                        <th scope="col" class="px-3 py-2 text-end">
                            <span class="sr-only">{{ __('forum_admin.moderation_operations.actions.open_case') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->reports as $report)
                        <tr wire:key="moderation-report-{{ $report['id'] }}" class="border-t border-paw-line">
                            <td class="px-3 py-3">
                                <span class="block font-medium">{{ $report['subject'] }}</span>
                                <span class="text-xs text-paw-muted">#{{ $report['subject_id'] }}</span>
                            </td>
                            <td class="px-3 py-3">{{ $report['reason'] }}</td>
                            <td class="px-3 py-3">{{ $report['priority'] }}</td>
                            <td class="px-3 py-3">{{ $report['status'] }}</td>
                            <td class="px-3 py-3">{{ $report['created'] }}</td>
                            <td class="px-3 py-3 text-end">
                                <button
                                    type="button"
                                    wire:click="openReport({{ $report['id'] }})"
                                    wire:loading.attr="disabled"
                                    wire:target="openReport({{ $report['id'] }})"
                                    class="forum-button"
                                >
                                    <x-ui-icon name="folder-plus" />
                                    <span>{{ __('forum_admin.moderation_operations.actions.open_case') }}</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-paw-muted">
                                {{ __('forum_admin.moderation_operations.reports.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section aria-labelledby="moderation-cases-heading">
        <div class="mb-3">
            <h2 id="moderation-cases-heading" class="text-xl font-semibold">
                {{ __('forum_admin.moderation_operations.cases.heading') }}
            </h2>
            <p class="mt-1 text-sm text-paw-muted">
                {{ __('forum_admin.moderation_operations.cases.summary') }}
            </p>
        </div>

        <div class="relative overflow-x-auto border-y border-paw-line bg-white">
            <table class="w-full min-w-[58rem] text-sm">
                <caption class="sr-only">{{ __('forum_accessibility.tables.moderation_cases') }}</caption>
                <thead class="bg-paw-paper">
                    <tr>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.case') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.priority') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.status') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.assignee') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.reports') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.actions') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.review_due') }}</th>
                        <th scope="col" class="px-3 py-2 text-end">
                            <span class="sr-only">{{ __('forum_admin.moderation_operations.actions.review_case') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->cases as $case)
                        <tr wire:key="moderation-case-{{ $case['id'] }}" class="border-t border-paw-line">
                            <td class="px-3 py-3 font-medium">{{ $case['number'] }}</td>
                            <td class="px-3 py-3">{{ $case['priority'] }}</td>
                            <td class="px-3 py-3">{{ $case['status'] }}</td>
                            <td class="px-3 py-3">{{ $case['assignee'] ?? __('forum_admin.moderation_operations.unassigned') }}</td>
                            <td class="px-3 py-3">{{ $case['reports'] }}</td>
                            <td class="px-3 py-3">{{ $case['actions'] }}</td>
                            <td class="px-3 py-3">{{ $case['review_due'] ?? __('forum_admin.moderation_operations.no_deadline') }}</td>
                            <td class="px-3 py-3 text-end">
                                <button
                                    type="button"
                                    wire:click="selectCase({{ $case['id'] }})"
                                    class="forum-button"
                                    aria-label="{{ __('forum_admin.moderation_operations.actions.review_named_case', ['case' => $case['number']]) }}"
                                >
                                    <x-ui-icon name="search" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-6 text-center text-paw-muted">
                                {{ __('forum_admin.moderation_operations.cases.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($this->selectedCase !== null)
        <section aria-labelledby="selected-case-heading" class="border-t border-paw-line pt-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-paw-muted">{{ __('forum_admin.moderation_operations.cases.selected') }}</p>
                    <h2 id="selected-case-heading" class="text-xl font-semibold">{{ $this->selectedCase['number'] }}</h2>
                    <p class="mt-1 text-sm">
                        {{ $this->selectedCase['priority'] }} · {{ $this->selectedCase['status'] }}
                    </p>
                </div>

                <form wire:submit="assignCase" class="flex flex-wrap items-end gap-2">
                    <label class="forum-form__field min-w-56">
                        <span>{{ __('forum_admin.moderation_operations.fields.assignee') }}</span>
                        <select wire:model="assigneeUserId" required>
                            <option value="">{{ __('forum_admin.moderation_operations.select_assignee') }}</option>
                            @foreach ($this->administrators as $administratorId => $administratorName)
                                <option value="{{ $administratorId }}">{{ $administratorName }}</option>
                            @endforeach
                        </select>
                        @error('assigneeUserId') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <button type="submit" wire:loading.attr="disabled" wire:target="assignCase" class="forum-button forum-button--primary">
                        <x-ui-icon name="user-check" />
                        <span wire:loading.remove wire:target="assignCase">{{ __('forum_admin.moderation_operations.actions.assign') }}</span>
                        <span wire:loading wire:target="assignCase">{{ __('forum_admin.actions.working') }}</span>
                    </button>
                </form>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)]">
                <div class="space-y-6">
                    <section aria-labelledby="case-reports-heading">
                        <h3 id="case-reports-heading" class="text-base font-semibold">
                            {{ __('forum_admin.moderation_operations.cases.linked_reports') }}
                        </h3>
                        <div class="mt-2 divide-y divide-paw-line border-y border-paw-line bg-white">
                            @forelse ($this->selectedCase['reports'] as $report)
                                <article wire:key="selected-case-report-{{ $report['id'] }}" class="px-3 py-4">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div>
                                            <h4 class="font-medium">{{ $report['reason'] }}</h4>
                                            <p class="text-xs text-paw-muted">
                                                {{ $report['subject'] }} #{{ $report['subject_id'] }} · {{ $report['priority'] }} · {{ $report['status'] }}
                                            </p>
                                        </div>
                                    </div>
                                    <p class="mt-2 whitespace-pre-wrap break-words text-sm">
                                        {{ $report['details'] ?? __('forum_admin.moderation_operations.no_details') }}
                                    </p>
                                    <ol class="mt-3 space-y-1 text-xs text-paw-muted" aria-label="{{ __('forum_admin.moderation_operations.fields.history') }}">
                                        @forelse ($report['events'] as $event)
                                            <li wire:key="report-event-{{ $event['id'] }}">{{ $event['type'] }} · {{ $event['created'] }}</li>
                                        @empty
                                            <li>{{ __('forum_admin.moderation_operations.no_events') }}</li>
                                        @endforelse
                                    </ol>
                                </article>
                            @empty
                                <p class="px-3 py-6 text-center text-paw-muted">{{ __('forum_admin.moderation_operations.reports.empty') }}</p>
                            @endforelse
                        </div>
                    </section>

                    <section aria-labelledby="case-actions-heading">
                        <h3 id="case-actions-heading" class="text-base font-semibold">
                            {{ __('forum_admin.moderation_operations.cases.action_history') }}
                        </h3>
                        <div class="mt-2 divide-y divide-paw-line border-y border-paw-line bg-white">
                            @forelse ($this->selectedCase['actions'] as $action)
                                <article wire:key="case-action-{{ $action['id'] }}" class="px-3 py-4 text-sm">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <h4 class="font-medium">{{ $action['definition'] }}</h4>
                                        <span>{{ $action['reversed'] ? __('forum_admin.moderation_operations.reversed') : __('forum_admin.moderation_operations.active') }}</span>
                                    </div>
                                    <p class="mt-1">{{ __('forum_admin.moderation_operations.fields.actor') }}: {{ $action['actor'] }}</p>
                                    <p>{{ __('forum_admin.moderation_operations.fields.target') }}: {{ $action['target'] ?? __('forum_admin.moderation_operations.no_target') }}</p>
                                    <p>{{ __('forum_admin.moderation_operations.fields.rule') }}: {{ $action['rule'] }}</p>
                                    <p>{{ __('forum_admin.moderation_operations.fields.policy') }}: {{ $action['policy'] }}</p>
                                    <p class="mt-2 whitespace-pre-wrap break-words text-paw-muted">{{ $action['internal_reason'] }}</p>
                                </article>
                            @empty
                                <p class="px-3 py-6 text-center text-paw-muted">{{ __('forum_admin.moderation_operations.cases.no_actions') }}</p>
                            @endforelse
                        </div>
                    </section>

                    <section aria-labelledby="case-recusals-heading">
                        <h3 id="case-recusals-heading" class="text-base font-semibold">
                            {{ __('forum_admin.moderation_operations.cases.recusals') }}
                        </h3>
                        <ul class="mt-2 divide-y divide-paw-line border-y border-paw-line bg-white text-sm">
                            @forelse ($this->selectedCase['recusals'] as $recusal)
                                <li wire:key="case-recusal-{{ $recusal['id'] }}" class="px-3 py-3">
                                    {{ $recusal['moderator'] }} · {{ $recusal['reason'] }} · {{ $recusal['created'] }}
                                </li>
                            @empty
                                <li class="px-3 py-4 text-paw-muted">{{ __('forum_admin.moderation_operations.cases.no_recusals') }}</li>
                            @endforelse
                        </ul>
                    </section>
                </div>

                <div class="space-y-8 border-s border-paw-line ps-5">
                    <form wire:submit="applyModerationAction" class="forum-form" aria-labelledby="moderation-action-heading">
                        <h3 id="moderation-action-heading" class="text-base font-semibold">
                            {{ __('forum_admin.moderation_operations.action_form.heading') }}
                        </h3>
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.moderation_operations.action_form.definition') }}</span>
                            <select wire:model="actionDefinitionId" required>
                                <option value="">{{ __('forum_admin.moderation_operations.action_form.select_definition') }}</option>
                                @foreach ($this->actionDefinitions as $definitionId => $definitionName)
                                    <option value="{{ $definitionId }}">{{ $definitionName }}</option>
                                @endforeach
                            </select>
                            @error('actionDefinitionId') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.moderation_operations.fields.rule') }}</span>
                            <input wire:model="actionRuleId" required minlength="3" maxlength="120">
                            @error('actionRuleId') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.moderation_operations.fields.policy') }}</span>
                            <input wire:model="actionPolicyBasis" required minlength="3" maxlength="180">
                            @error('actionPolicyBasis') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.moderation_operations.action_form.internal_reason') }}</span>
                            <textarea wire:model="actionInternalReason" required minlength="20" maxlength="2000" rows="5"></textarea>
                            <small>{{ __('forum_admin.moderation_operations.action_form.private_help') }}</small>
                            @error('actionInternalReason') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.moderation_operations.action_form.ends_at') }}</span>
                            <input type="datetime-local" wire:model="actionEndsAt">
                            @error('actionEndsAt') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.moderation_operations.action_form.senior_approver') }}</span>
                            <select wire:model="seniorApproverId">
                                <option value="">{{ __('forum_admin.moderation_operations.action_form.no_approver') }}</option>
                                @foreach ($this->administrators as $administratorId => $administratorName)
                                    <option value="{{ $administratorId }}">{{ $administratorName }}</option>
                                @endforeach
                            </select>
                            @error('seniorApproverId') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <button
                            type="submit"
                            wire:confirm="{{ __('forum_admin.moderation_operations.action_form.confirm') }}"
                            wire:loading.attr="disabled"
                            wire:target="applyModerationAction"
                            class="forum-button forum-button--primary"
                        >
                            <x-ui-icon name="shield-check" />
                            <span wire:loading.remove wire:target="applyModerationAction">{{ __('forum_admin.moderation_operations.actions.record_action') }}</span>
                            <span wire:loading wire:target="applyModerationAction">{{ __('forum_admin.actions.working') }}</span>
                        </button>
                    </form>

                    <form wire:submit="recuseFromCase" class="forum-form border-t border-paw-line pt-6" aria-labelledby="recusal-heading">
                        <h3 id="recusal-heading" class="text-base font-semibold">
                            {{ __('forum_admin.moderation_operations.recusal.heading') }}
                        </h3>
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.moderation_operations.recusal.reason') }}</span>
                            <select wire:model="recusalReason" required>
                                <option value="personally-involved">{{ __('forum_admin.moderation_operations.recusal.personally-involved') }}</option>
                                <option value="connected-party">{{ __('forum_admin.moderation_operations.recusal.connected-party') }}</option>
                                <option value="organization-conflict">{{ __('forum_admin.moderation_operations.recusal.organization-conflict') }}</option>
                                <option value="financial-interest">{{ __('forum_admin.moderation_operations.recusal.financial-interest') }}</option>
                                <option value="prior-public-dispute">{{ __('forum_admin.moderation_operations.recusal.prior-public-dispute') }}</option>
                                <option value="responsible-for-content">{{ __('forum_admin.moderation_operations.recusal.responsible-for-content') }}</option>
                                <option value="unable-to-remain-impartial">{{ __('forum_admin.moderation_operations.recusal.unable-to-remain-impartial') }}</option>
                            </select>
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.moderation_operations.recusal.private_note') }}</span>
                            <textarea wire:model="recusalPrivateNote" maxlength="2000" rows="3"></textarea>
                            @error('recusalPrivateNote') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <button type="submit" wire:loading.attr="disabled" wire:target="recuseFromCase" class="forum-button">
                            <x-ui-icon name="user-x" />
                            <span>{{ __('forum_admin.moderation_operations.actions.recuse') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    @endif

    <section aria-labelledby="appeals-heading" class="border-t border-paw-line pt-6">
        <h2 id="appeals-heading" class="text-xl font-semibold">
            {{ __('forum_admin.moderation_operations.appeals.heading') }}
        </h2>
        <p class="mt-1 text-sm text-paw-muted">{{ __('forum_admin.moderation_operations.appeals.summary') }}</p>

        <div class="relative mt-3 overflow-x-auto border-y border-paw-line bg-white">
            <table class="w-full min-w-[52rem] text-sm">
                <caption class="sr-only">{{ __('forum_accessibility.tables.appeals') }}</caption>
                <thead class="bg-paw-paper">
                    <tr>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.case') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.appeals.appellant') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.appeals.action') }}</th>
                        <th scope="col" class="px-3 py-2 text-start">{{ __('forum_admin.moderation_operations.fields.created') }}</th>
                        <th scope="col" class="px-3 py-2 text-end"><span class="sr-only">{{ __('forum_admin.moderation_operations.appeals.review') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->appeals as $appeal)
                        <tr wire:key="moderation-appeal-{{ $appeal['id'] }}" class="border-t border-paw-line">
                            <td class="px-3 py-3 font-medium">{{ $appeal['case'] }}</td>
                            <td class="px-3 py-3">{{ $appeal['appellant'] }}</td>
                            <td class="px-3 py-3">
                                <span class="block">{{ $appeal['action'] }}</span>
                                <span class="forum-badge mt-1">{{ $appeal['status'] }}</span>
                                <span class="block max-w-xl whitespace-pre-wrap break-words text-xs text-paw-muted">{{ $appeal['reason'] }}</span>
                            </td>
                            <td class="px-3 py-3">{{ $appeal['submitted'] }}</td>
                            <td class="px-3 py-3 text-end">
                                <button type="button" wire:click="selectAppeal({{ $appeal['id'] }})" class="forum-button">
                                    <x-ui-icon name="scale" />
                                    <span>{{ __('forum_admin.moderation_operations.appeals.review') }}</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-paw-muted">
                                {{ __('forum_admin.moderation_operations.appeals.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($selectedAppealId !== null)
            <form wire:submit="reviewAppeal" class="forum-form mt-5 max-w-2xl" aria-labelledby="appeal-review-heading">
                <h3 id="appeal-review-heading" class="text-base font-semibold">
                    {{ __('forum_admin.moderation_operations.appeals.review') }}
                </h3>
                <label class="forum-form__field">
                    <span>{{ __('forum_admin.moderation_operations.appeals.outcome') }}</span>
                    <select wire:model="appealOutcome" required>
                        <option value="upheld">{{ __('forum_admin.moderation_operations.appeal_outcome.upheld') }}</option>
                        <option value="modified">{{ __('forum_admin.moderation_operations.appeal_outcome.modified') }}</option>
                        <option value="reversed">{{ __('forum_admin.moderation_operations.appeal_outcome.reversed') }}</option>
                        <option value="new-review">{{ __('forum_admin.moderation_operations.appeal_outcome.new-review') }}</option>
                    </select>
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_admin.moderation_operations.appeals.decision_reason') }}</span>
                    <textarea wire:model="appealDecisionReason" required minlength="20" maxlength="2000" rows="5"></textarea>
                    @error('appealDecisionReason') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <button
                    type="submit"
                    wire:confirm="{{ __('forum_admin.moderation_operations.appeals.confirm') }}"
                    wire:loading.attr="disabled"
                    wire:target="reviewAppeal"
                    class="forum-button forum-button--primary"
                >
                    <x-ui-icon name="scale" />
                    <span wire:loading.remove wire:target="reviewAppeal">{{ __('forum_admin.moderation_operations.appeals.record_decision') }}</span>
                    <span wire:loading wire:target="reviewAppeal">{{ __('forum_admin.actions.working') }}</span>
                </button>
            </form>
        @endif
    </section>
</div>
