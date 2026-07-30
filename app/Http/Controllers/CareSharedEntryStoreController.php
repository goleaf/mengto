<?php

namespace App\Http\Controllers;

use App\Actions\CreateCareEntry;
use App\Actions\ResolveCareAccess;
use App\Enums\CareEntryType;
use App\Http\Requests\StoreSharedCareEntryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class CareSharedEntryStoreController extends Controller
{
    public function __invoke(
        StoreSharedCareEntryRequest $request,
        string $token,
        ResolveCareAccess $resolve,
        CreateCareEntry $create,
    ): RedirectResponse {
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
                'location_label' => 'This access link does not allow location details.',
            ]);
        }

        if (! $grant->allow_media && isset($data['media'])) {
            throw ValidationException::withMessages([
                'media' => 'This access link does not allow media uploads.',
            ]);
        }

        $source = match ($grant->recipient_role) {
            'co-owner', 'family' => 'family',
            'sitter' => 'sitter',
            default => 'specialist',
        };

        $create->handle($grant->careJournal, [
            ...$data,
            'source_type' => $source,
            'source_name' => $grant->recipient_name,
        ], [
            'key' => $grant->recipient_key ?? 'care-grant-'.$grant->id,
            'name' => $grant->recipient_name,
            'role' => $source,
        ]);

        return to_route('care-access.show', ['token' => $token])
            ->with('feedback', 'Care report added with your name and contributor role.');
    }
}
