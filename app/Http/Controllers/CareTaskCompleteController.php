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
                'task' => 'This task does not belong to the selected care journal.',
            ]);
        }

        $complete->handle($careJournal, $careTask, $request->validated());

        return to_route('care-journals.show', $careJournal)
            ->with('feedback', 'Task outcome recorded once in the care timeline.');
    }
}
