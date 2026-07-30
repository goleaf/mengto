<?php

namespace App\Http\Controllers;

use App\Actions\ProposeCorrection;
use App\Http\Requests\StoreCorrectionRequest;
use App\Models\KnowledgeArticle;
use Illuminate\Http\RedirectResponse;

class CorrectionStoreController extends Controller
{
    public function __invoke(
        StoreCorrectionRequest $request,
        KnowledgeArticle $knowledgeArticle,
        ProposeCorrection $proposeCorrection,
    ): RedirectResponse {
        $proposeCorrection->handle($knowledgeArticle, $request->validated());

        return to_route('pet-social.knowledge.articles.show', $knowledgeArticle)
            ->with('pawcircle.feedback', 'Correction sent to the editorial queue.');
    }
}
