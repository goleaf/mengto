<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use App\Services\KnowledgeGuideExporter;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class KnowledgeGuideExportController extends Controller
{
    public function __invoke(
        Request $request,
        KnowledgeArticle $knowledgeArticle,
        Gate $gate,
        KnowledgeGuideExporter $exporter,
        ResponseFactory $responses,
    ): Response {
        $gate->forUser($request->user())->authorize('export', $knowledgeArticle);

        return $responses->make(
            $exporter->markdown($knowledgeArticle),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/markdown; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$knowledgeArticle->slug.'.md"',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
