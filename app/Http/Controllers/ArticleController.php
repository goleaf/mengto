<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use App\Services\KnowledgePresenter;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\View\View;

final class ArticleController extends Controller
{
    public function __invoke(
        KnowledgeArticle $knowledgeArticle,
        KnowledgePresenter $presenter,
        Gate $gate,
    ): View {
        abort_unless($gate->allows('view', $knowledgeArticle), 404);

        return view('knowledge.show', $presenter->article($knowledgeArticle));
    }
}
