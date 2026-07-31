<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use App\Services\KnowledgePresenter;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class KnowledgeGuidePrintController extends Controller
{
    public function __invoke(
        Request $request,
        KnowledgeArticle $knowledgeArticle,
        Gate $gate,
        KnowledgePresenter $presenter,
        Application $application,
    ): View {
        $gate->forUser($request->user())->authorize('export', $knowledgeArticle);

        return view('knowledge.print', [
            ...$presenter->article($knowledgeArticle),
            'document_locale' => $application->getLocale(),
        ]);
    }
}
