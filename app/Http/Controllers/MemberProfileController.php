<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SocialActorType;
use App\Models\SocialActor;
use App\Models\User;
use App\Services\MemberProfileCatalog;
use App\Services\ProfilePresenter;
use App\Services\SocialBlockService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class MemberProfileController extends Controller
{
    public function __invoke(
        Request $request,
        SocialActor $socialActor,
        Gate $gate,
        MemberProfileCatalog $members,
        ProfilePresenter $profiles,
        SocialBlockService $blocks,
    ): View {
        $socialActor->loadMissing('user:id,name,status,email_verified_at,created_at');
        $member = $socialActor->user;

        abort_unless(
            $socialActor->actor_type === SocialActorType::User
                && $member instanceof User
                && $member->isActive()
                && $member->hasVerifiedEmail(),
            404,
        );
        $gate->authorize('view', $socialActor);
        $viewer = $request->user();

        if ($viewer instanceof User
            && in_array($socialActor->id, $blocks->blockedActorIdsFor($viewer), true)
        ) {
            abort(404);
        }

        return view('members.show', [
            'owner' => $profiles->owner(),
            'page_title' => __('member_profiles.page.browser_title', ['name' => $member->name]),
            'profile' => $members->present(
                $socialActor,
                $viewer instanceof User ? $viewer : null,
            ),
        ]);
    }
}
