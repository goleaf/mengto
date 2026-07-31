<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ForumAdministrationController extends Controller
{
    public function __invoke(Request $request, PreviewService $preview): View
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isAdministrator(), 403);

        return view('forum.admin', [
            'owner' => $preview->ownerData(),
            'page_title' => __('forum_admin.title'),
            'active_section' => 'forum',
        ]);
    }
}
