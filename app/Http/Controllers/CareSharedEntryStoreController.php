<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateCareEntry;
use App\Actions\ResolveCareAccess;
use App\Enums\CareEntryType;
use App\Http\Requests\StoreSharedCareEntryRequest;
use App\Models\CareAccessGrant;
use App\Services\ForumActor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class CareSharedEntryStoreController extends Controller
{
    public function __invoke(
        StoreSharedCareEntryRequest $request,
        string $token,
        ResolveCareAccess $resolve,
        CreateCareEntry $create,
        ForumActor $actor,
    ): JsonResponse|RedirectResponse {
        $data = $request->validated();
        $type = CareEntryType::from($data['entry_type']);
        $identity = $actor->identity();

        $entry = $resolve->execute(
            $token,
            'care-access.entry-submitted',
            function (CareAccessGrant $grant) use ($create, $data, $identity, $type) {
                if (! $grant->canAdd() || ! $grant->canViewSection($type->section())) {
                    abort(403);
                }

                if (! $grant->allow_location && (
                    filled($data['location_label'] ?? null)
                    || filled($data['route_summary'] ?? null)
                )) {
                    throw ValidationException::withMessages([
                        'location_label' => __('messages.this_access_link_does_not_allow_location_details'),
                    ]);
                }

                if (! $grant->allow_media && isset($data['media'])) {
                    throw ValidationException::withMessages([
                        'media' => __('messages.this_access_link_does_not_allow_media_uploads'),
                    ]);
                }

                $source = match ($grant->recipient_role) {
                    'co-owner', 'family' => 'family',
                    'sitter' => 'sitter',
                    default => 'specialist',
                };

                return $create->handle($grant->careJournal, [
                    ...$data,
                    'source_type' => $source,
                    'source_name' => $identity['name'],
                ], [
                    'key' => $identity['key'],
                    'name' => $identity['name'],
                    'role' => $source,
                ], $grant);
            },
            false,
            false,
        );
        $message = __('messages.care_report_added_with_your_name_and_contributor_role');

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

        return to_route('care-access.show', ['token' => $token])
            ->with('feedback', $message);
    }
}
