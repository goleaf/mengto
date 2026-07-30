<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateCareEntry;
use App\Actions\ResolveCareAccess;
use App\Enums\CareEntryType;
use App\Http\Requests\StoreSharedCareEntryRequest;
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
    ): JsonResponse|RedirectResponse {
        $grant = $resolve->handle($token, 'care-access.entry-submitted', false);
        $data = $request->validated();
        $type = CareEntryType::from($data['entry_type']);

        if (! $grant->canAdd() || ! $grant->canViewSection($type->section())) {
            abort(403);
        }

        if (! $grant->allow_location && (
            filled($data['location_label'] ?? null)
            || filled($data['route_summary'] ?? null)
        )) {
            throw ValidationException::withMessages([
                'location_label' => __('messages.this_access_link_does_not_allow_location_details_c4899af7a0'),
            ]);
        }

        if (! $grant->allow_media && isset($data['media'])) {
            throw ValidationException::withMessages([
                'media' => __('messages.this_access_link_does_not_allow_media_uploads_9ff792ddc7'),
            ]);
        }

        $source = match ($grant->recipient_role) {
            'co-owner', 'family' => 'family',
            'sitter' => 'sitter',
            default => 'specialist',
        };

        $entry = $create->handle($grant->careJournal, [
            ...$data,
            'source_type' => $source,
            'source_name' => $grant->recipient_name,
        ], [
            'key' => $grant->recipient_key ?? 'care-grant-'.$grant->id,
            'name' => $grant->recipient_name,
            'role' => $source,
        ]);
        $message = __('messages.care_report_added_with_your_name_and_contributor_role_2ad163127b');

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
