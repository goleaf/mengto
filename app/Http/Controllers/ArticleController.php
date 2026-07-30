<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use App\Services\KnowledgePresenter;
use Illuminate\Contracts\View\View;

class ArticleController extends Controller
{
    public function __invoke(KnowledgeArticle $knowledgeArticle, KnowledgePresenter $presenter): View
    {
        abort_unless(in_array($knowledgeArticle->status->value, ['published', 'outdated'], true), 404);

        return view('pet-social.knowledge.show', $presenter->article($knowledgeArticle));
    }
}
