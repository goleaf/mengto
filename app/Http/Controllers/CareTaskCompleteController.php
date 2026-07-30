<?php

namespace App\Http\Controllers;

use App\Actions\CompleteCareTask;
use App\Http\Requests\StoreCareTaskCompletionRequest;
use App\Models\CareJournal;
use App\Models\CareTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CareTaskCompleteController extends Controller
{
    public function __invoke(
        StoreCareTaskCompletionRequest $request,
        CareJournal $careJournal,
        CareTask $careTask,
        CompleteCareTask $complete,
    ): RedirectResponse {
        Gate::authorize('update', $careJournal);

        if ($careTask->care_journal_id !== $careJournal->id) {
            throw ValidationException::withMessages([
                'task' => __('messages.this_task_does_not_belong_to_the_selected_care_journal_62cdb67e40'),
            ]);
        }

        $complete->handle($careJournal, $careTask, $request->validated());

        return to_route('care-journals.show', $careJournal)
            ->with('feedback', __('messages.task_outcome_recorded_once_in_the_care_timeline_fd2d2831a3'));
    }
}
