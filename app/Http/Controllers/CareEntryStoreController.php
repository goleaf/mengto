<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateCareEntry;
use App\Http\Requests\StoreCareEntryRequest;
use App\Models\CareJournal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CareEntryStoreController extends Controller
{
    public function __invoke(
        StoreCareEntryRequest $request,
        CareJournal $careJournal,
        CreateCareEntry $create,
    ): JsonResponse|RedirectResponse {
        Gate::authorize('update', $careJournal);
        $entry = $create->handle($careJournal, $request->validated());
        $message = __('messages.care_action_recorded_with_its_author_source_and_exact_ti_f88190bcb3');

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'id' => $entry->id,
                    'idempotency_key' => $entry->idempotency_key,
                    'sync_status' => $entry->sync_status->value,
                ],
                'message' => $message,
            ]);
        }

        return to_route('care-journals.show', $careJournal)
            ->with('feedback', $message);
    }
}
