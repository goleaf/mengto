<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class KnowledgeGuideEditController extends Controller
{
    public function __invoke(
        Request $request,
        KnowledgeArticle $knowledgeArticle,
        Gate $gate,
        ProfilePresenter $profiles,
    ): View {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $gate->forUser($user)->authorize('update', $knowledgeArticle);

        return view('knowledge.editor', [
            'owner' => $profiles->owner(),
            'page_title' => __('knowledge.editor.edit_page_title', [
                'title' => $knowledgeArticle->title,
            ]),
            'active_section' => 'forum',
            'article_id' => $knowledgeArticle->id,
            'source_article_id' => null,
        ]);
    }
}
