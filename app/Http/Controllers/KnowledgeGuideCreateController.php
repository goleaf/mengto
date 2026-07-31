<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class KnowledgeGuideCreateController extends Controller
{
    public function __invoke(
        Request $request,
        Gate $gate,
        ProfilePresenter $profiles,
    ): View {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $gate->forUser($user)->authorize('create', KnowledgeArticle::class);

        return view('knowledge.editor', [
            'owner' => $profiles->owner(),
            'page_title' => __('knowledge.editor.create_page_title'),
            'active_section' => 'forum',
            'article_id' => null,
        ]);
    }
}
