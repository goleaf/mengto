<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SocialActorType;
use App\Models\SocialActor;
use App\Models\User;
use App\Services\EmailVerificationMode;
use App\Services\MemberProfileCatalog;
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
        EmailVerificationMode $emailVerification,
        MemberProfileCatalog $members,
        SocialBlockService $blocks,
    ): View {
        $socialActor->loadMissing('user:id,name,status,email_verified_at,created_at');
        $member = $socialActor->user;
        $viewer = $request->user();

        abort_unless(
            $socialActor->actor_type === SocialActorType::User
                && $member instanceof User
                && $member->isActive()
                && $emailVerification->allows($member),
            404,
        );

        if ($viewer instanceof User && (
            $blocks->blockedForContact($viewer, $socialActor)
            || $blocks->actorBlockedForContact($viewer, $socialActor)
        )) {
            abort(404);
        }

        abort_unless($gate->allows('view', $socialActor), 404);

        return view('members.show', [
            'active_section' => $viewer instanceof User && $viewer->is($member)
                ? 'profile'
                : 'discover',
            'page_title' => __('member_profiles.page.browser_title', ['name' => $member->name]),
            'profile' => $members->present(
                $socialActor,
                $viewer instanceof User ? $viewer : null,
            ),
        ]);
    }
}
