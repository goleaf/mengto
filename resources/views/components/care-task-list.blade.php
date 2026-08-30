@props(['tasks', 'journalSlug' => null, 'readOnly' => false])

<div class="care-task-list">
    @forelse ($tasks as $task)
        <article class="care-task {{ $task['is_overdue'] ? 'care-task--overdue' : '' }}">
            <span class="care-task__icon">
                <x-ui-icon size="sm" :name="$task['icon']" />
            </span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h3 class="font-bold">{{ $task['title'] }}</h3>
                    <x-status-badge :label="$task['status_label']" :icon="$task['is_overdue'] ? 'clock-alert' : 'clock-3'" :tone="$task['is_overdue'] ? 'warning' : 'surface'" />
                </div>
                <p class="mt-1 text-sm text-paw-muted">{{ $task['due_at'] }} · {{ $task['assignee'] }}</p>
                <p class="mt-1 text-xs font-semibold text-paw-muted">{{ $task['priority'] }} · {{ $task['type_label'] }}</p>
                @if ($task['instructions'])
                    <p class="mt-2 text-sm leading-6 text-paw-muted">{{ $task['instructions'] }}</p>
                @endif

                @if (! $readOnly && $journalSlug)
                    <form method="POST" action="{{ route('care-journals.tasks.complete', [$journalSlug, $task['id']]) }}" class="care-task__complete">
                        @csrf
                        <input type="hidden" name="idempotency_key" value="{{ $task['idempotency_key'] }}">
                        <select name="status" aria-label="{{ __('presentation.task_outcome', ['task' => $task['title']]) }}">
                            <option value="completed">{{ __('ui.completed') }}</option>
                            <option value="partial">{{ __('ui.partially_completed') }}</option>
                            <option value="refused">{{ __('ui.pet_refused') }}</option>
                            <option value="skipped">{{ __('ui.skipped') }}</option>
                            <option value="needs-help">{{ __('ui.needs_help') }}</option>
                        </select>
                        <input name="completion_note" maxlength="2000" placeholder="{{ __('ui.optional_outcome_note') }}" aria-label="{{ __('ui.completion_note') }}">
                        <button type="submit" class="action action--primary action--compact">
                            <x-ui-icon name="check" size="sm" />
                            <span>{{ __('ui.record') }}</span>
                        </button>
                    </form>
                @endif
            </div>
        </article>
    @empty
        <div class="care-empty">
            <x-ui-icon name="list-checks" size="xl" />
            <p>{{ __('ui.no_open_tasks_unrecorded_care_is_not_assumed_to_be_missed') }}</p>
        </div>
    @endforelse
</div>
