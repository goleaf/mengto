<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\KnowledgeGuideData;
use App\Enums\KnowledgeCollaboratorRole;
use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeTranslationSource;
use App\Enums\KnowledgeWorkflowEventType;
use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Services\KnowledgeGuideHistory;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateKnowledgeGuideTranslation
{
    public function __construct(
        private readonly Gate $gate,
        private readonly KnowledgeGuideHistory $history,
    ) {}

    public function handle(
        User $actor,
        KnowledgeArticle $source,
        KnowledgeGuideData $data,
    ): KnowledgeArticle {
        $this->gate->forUser($actor)->authorize('translate', $source);
        $this->validateLocale($source, $data->language);

        try {
            return DB::transaction(function () use ($actor, $data, $source): KnowledgeArticle {
                $lockedSource = KnowledgeArticle::query()
                    ->forEditor()
                    ->lockForUpdate()
                    ->findOrFail($source->id);
                $this->gate->forUser($actor)->authorize('translate', $lockedSource);
                $this->validateLocale($lockedSource, $data->language);

                $groupKey = $lockedSource->translation_group_key;

                if ($groupKey === null) {
                    throw ValidationException::withMessages([
                        'form.language' => __('knowledge.validation.translation_family_missing'),
                    ]);
                }

                if (
                    KnowledgeArticle::query()
                        ->where('translation_group_key', $groupKey)
                        ->where('language', $data->language)
                        ->exists()
                ) {
                    throw $this->duplicateLocaleException();
                }

                $article = KnowledgeArticle::query()->create([
                    'created_by_user_id' => $actor->id,
                    'forum_group_id' => $lockedSource->forum_group_id,
                    'source_topic_id' => $lockedSource->source_topic_id,
                    'discussion_topic_id' => $lockedSource->discussion_topic_id,
                    'taxon_id' => $lockedSource->taxon_id,
                    'translated_from_article_id' => $lockedSource->id,
                    'translated_by_user_id' => $actor->id,
                    'slug' => Str::slug($data->title).'-'.Str::lower(Str::random(8)),
                    'translation_group_key' => $groupKey,
                    'translation_source' => KnowledgeTranslationSource::HumanCommunity,
                    'title' => trim($data->title),
                    'summary' => trim($data->summary),
                    'body' => trim($data->body),
                    'category' => $lockedSource->category,
                    'type' => $lockedSource->type,
                    'difficulty' => $lockedSource->difficulty,
                    'audience' => $data->audience,
                    'status' => KnowledgeStatus::Draft,
                    'language' => $data->language,
                    'jurisdiction' => $lockedSource->jurisdiction,
                    'sources' => $data->sources,
                    'protected_sections' => $data->protectedSections,
                    'tags' => $lockedSource->tags ?? [],
                    'contributors' => [],
                    'current_version' => 1,
                    'lock_version' => 0,
                ]);

                $article->collaborators()->create([
                    'user_id' => $actor->id,
                    'role' => KnowledgeCollaboratorRole::Maintainer,
                    'added_by_user_id' => $actor->id,
                    'attribution_name' => $actor->name,
                ]);
                $this->history->snapshot($article, $actor, $data->changeSummary);
                $this->history->record(
                    $article,
                    $actor,
                    KnowledgeWorkflowEventType::Created,
                    'guide-translation-created',
                    'knowledge.events.translation_created',
                    versionNumber: 1,
                    metadata: [
                        'source_article_id' => $lockedSource->id,
                        'source_locale' => $lockedSource->language,
                        'target_locale' => $data->language,
                        'translation_source' => KnowledgeTranslationSource::HumanCommunity->value,
                    ],
                );

                return $article->refresh();
            }, 3);
        } catch (QueryException $exception) {
            if (
                $source->translation_group_key !== null
                && KnowledgeArticle::query()
                    ->where('translation_group_key', $source->translation_group_key)
                    ->where('language', $data->language)
                    ->exists()
            ) {
                throw $this->duplicateLocaleException();
            }

            throw $exception;
        }
    }

    private function validateLocale(
        KnowledgeArticle $source,
        string $targetLocale,
    ): void {
        $supported = config('platform.supported_locales', ['en']);

        if (
            ! in_array($targetLocale, $supported, true)
            || $targetLocale === $source->language
        ) {
            throw ValidationException::withMessages([
                'form.language' => __('knowledge.validation.invalid_translation_locale'),
            ]);
        }
    }

    private function duplicateLocaleException(): ValidationException
    {
        return ValidationException::withMessages([
            'form.language' => __('knowledge.validation.translation_locale_exists'),
        ]);
    }
}
