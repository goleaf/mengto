<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\KnowledgeArticle;

final class KnowledgeGuideExporter
{
    public function markdown(KnowledgeArticle $article): string
    {
        $article->loadMissing([
            'activeCollaborators.user:id,name',
            'taxon.activeVersion:id,taxon_id,scientific_name,rank,is_active_version',
        ]);
        $lines = [
            "# {$article->title}",
            '',
            $article->summary,
            '',
            $article->body,
            '',
            '---',
            '',
            __('knowledge.export.status', ['status' => $article->status->label()]),
            __('knowledge.export.locale', ['locale' => $article->language]),
        ];

        if ($article->jurisdiction !== null) {
            $lines[] = __('knowledge.export.jurisdiction', [
                'jurisdiction' => $article->jurisdiction,
            ]);
        }

        if ($article->taxon?->activeVersion !== null) {
            $lines[] = __('knowledge.export.taxon', [
                'taxon' => $article->taxon->activeVersion->scientific_name,
            ]);
        }

        $attribution = $article->activeCollaborators
            ->map(fn ($collaborator): string => $collaborator->attribution_name
                ?? $collaborator->user->name)
            ->unique()
            ->implode(', ');

        if ($attribution !== '') {
            $lines[] = __('knowledge.export.contributors', [
                'contributors' => $attribution,
            ]);
        }

        if (($article->sources ?? []) !== []) {
            $lines[] = '';
            $lines[] = '## '.__('knowledge.export.sources_heading');

            foreach ($article->sources ?? [] as $source) {
                $lines[] = "- {$source}";
            }
        }

        return implode("\n", $lines)."\n";
    }
}
