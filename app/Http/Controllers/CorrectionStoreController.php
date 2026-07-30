<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ProposeCorrection;
use App\Http\Requests\StoreCorrectionRequest;
use App\Models\KnowledgeArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CorrectionStoreController extends Controller
{
    public function __invoke(
        StoreCorrectionRequest $request,
        KnowledgeArticle $knowledgeArticle,
        ProposeCorrection $proposeCorrection,
    ): RedirectResponse {
        Gate::authorize('proposeCorrection', $knowledgeArticle);
        $proposeCorrection->handle($knowledgeArticle, $request->validated());

        return to_route('knowledge.articles.show', $knowledgeArticle)
            ->with('feedback', __('knowledge.feedback.correction_submitted'));
    }
}
