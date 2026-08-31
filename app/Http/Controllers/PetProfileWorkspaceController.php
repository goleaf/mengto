<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DiscoveryCategory;
use App\Http\Requests\BrowsePetProfilesRequest;
use App\Models\PetProfile;
use App\Models\User;
use App\Services\PetProfileWorkspace;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\View\View;

final class PetProfileWorkspaceController extends Controller
{
    public function __invoke(
        BrowsePetProfilesRequest $request,
        Gate $gate,
        PetProfileWorkspace $workspace,
    ): View {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $gate->forUser($user)->authorize('viewAny', PetProfile::class);

        return view('pets.index', [
            'discoverPetsUrl' => route('discover.index', [
                'category' => DiscoveryCategory::Pets->value,
            ]),
            ...$workspace->browse($user, $request->validated()),
        ]);
    }
}
