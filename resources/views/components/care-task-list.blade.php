@props(['tasks', 'journalSlug' => null, 'readOnly' => false])

<div class="care-task-list">
    @forelse ($tasks as $task)
        <article class="care-task {{ $task['is_overdue'] ? 'care-task--overdue' : '' }}">
            <span class="care-task__icon">
                <x-dynamic-component :component="'lucide-'.$task['icon']" class="size-4" aria-hidden="true" />
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
                        <select name="status" aria-label="Outcome for {{ $task['title'] }}">
                            <option value="completed">Completed</option>
                            <option value="partial">Partially completed</option>
                            <option value="refused">Pet refused</option>
                            <option value="skipped">Skipped</option>
                            <option value="needs-help">Needs help</option>
                        </select>
                        <input name="completion_note" maxlength="2000" placeholder="Optional outcome note" aria-label="Completion note">
                        <button type="submit" class="action action--primary action--compact">
                            <x-lucide-check class="icon icon--sm" aria-hidden="true" />
                            <span>Record</span>
                        </button>
                    </form>
                @endif
            </div>
        </article>
    @empty
        <div class="care-empty">
            <x-lucide-list-checks class="size-7" aria-hidden="true" />
            <p>No open tasks. Unrecorded care is not assumed to be missed.</p>
        </div>
    @endforelse
</div>
