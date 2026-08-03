<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateDiscoveryPreference;
use App\Enums\DiscoveryCategory;
use App\Enums\DiscoveryPreferenceScope;
use App\Http\Requests\UpdateDiscoveryPreferenceRequest;
use App\Models\DiscoveryPreference;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class DiscoveryPreferenceController extends Controller
{
    public function __invoke(
        UpdateDiscoveryPreferenceRequest $request,
        UpdateDiscoveryPreference $preferences,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $data = $request->validated();

        if ($data['action'] === 'reset') {
            Gate::authorize('deleteAny', DiscoveryPreference::class);
            $preferences->reset($user);
            $message = __('discovery.feedback.reset');
        } else {
            Gate::authorize('create', DiscoveryPreference::class);
            $preferences->hide(
                user: $user,
                scope: DiscoveryPreferenceScope::from($data['scope']),
                category: DiscoveryCategory::from($data['category']),
                targetKey: $data['target_key'] ?? null,
                reasonCode: $data['reason_code'] ?? null,
            );
            $message = $data['scope'] === DiscoveryPreferenceScope::Category->value
                ? __('discovery.feedback.category_hidden')
                : __('discovery.feedback.item_hidden');
        }

        return redirect()
            ->route('discover.index', array_filter([
                'q' => $data['return_q'] ?? null,
                'category' => $data['return_category'] ?? null,
            ], static fn (mixed $value): bool => is_string($value) && $value !== ''))
            ->with('feedback', $message);
    }
}
