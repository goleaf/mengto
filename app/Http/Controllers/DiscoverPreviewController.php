<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DiscoveryCategory;
use App\Http\Requests\BrowseDiscoveryRequest;
use App\Models\User;
use App\Services\DiscoveryCatalog;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\View\View;

final class DiscoverPreviewController extends Controller
{
    public function __invoke(
        BrowseDiscoveryRequest $request,
        DiscoveryCatalog $discovery,
        ProfilePresenter $profiles,
    ): View {
        $parameters = $request->validated();
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $category = DiscoveryCategory::from($parameters['category'] ?? 'all');
        $query = trim((string) ($parameters['q'] ?? ''));

        return view('discover.index', [
            'owner' => $profiles->owner(),
            ...$discovery->browse($user, $query, $category),
        ]);
    }
}
