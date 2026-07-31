<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeTranslationSource;
use App\Models\ForumGroup;
use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<KnowledgeArticle>
 */
class KnowledgeArticleFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(7);

        return [
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'translation_group_key' => 'guide-'.fake()->unique()->uuid(),
            'title' => $title,
            'summary' => fake()->paragraph(),
            'body' => fake()->paragraphs(5, true),
            'category' => fake()->randomElement(['behavior', 'travel', 'health', 'care']),
            'type' => 'guide',
            'difficulty' => 'beginner',
            'audience' => 'Pet owners',
            'status' => KnowledgeStatus::Published,
            'language' => 'en',
            'tags' => [fake()->word(), fake()->word()],
            'sources' => [],
            'contributors' => ['PawCircle editorial team'],
            'current_version' => 1,
            'last_reviewed_at' => now(),
            'next_review_at' => now()->addMonths(6),
            'published_at' => now(),
            'lock_version' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeStatus::Draft,
            'published_at' => null,
            'last_reviewed_at' => null,
        ]);
    }

    public function submittedForReview(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeStatus::SubmittedForReview,
            'published_at' => null,
        ]);
    }

    public function changesRequested(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeStatus::ChangesRequested,
            'published_at' => null,
        ]);
    }

    public function communityReviewed(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeStatus::CommunityReviewed,
            'published_at' => null,
        ]);
    }

    public function expertReviewed(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeStatus::ExpertReviewed,
            'published_at' => null,
        ]);
    }

    public function correctionRequested(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeStatus::CorrectionRequested,
        ]);
    }

    public function outdated(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeStatus::Outdated,
            'published_at' => now()->subYear(),
            'next_review_at' => now()->subDay(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeStatus::Archived,
            'published_at' => null,
        ]);
    }

    public function replaced(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeStatus::Replaced,
            'published_at' => null,
        ]);
    }

    public function forGroup(?ForumGroup $group = null): static
    {
        return $this->state(fn (): array => [
            'forum_group_id' => $group === null ? ForumGroup::factory() : $group->id,
        ]);
    }

    public function translatedFrom(
        KnowledgeArticle $source,
        ?User $translator = null,
        string $language = 'lt',
    ): static {
        return $this->state(fn (): array => [
            'created_by_user_id' => $translator?->id,
            'forum_group_id' => $source->forum_group_id,
            'source_topic_id' => $source->source_topic_id,
            'discussion_topic_id' => $source->discussion_topic_id,
            'taxon_id' => $source->taxon_id,
            'translated_from_article_id' => $source->id,
            'translated_by_user_id' => $translator?->id,
            'translation_group_key' => $source->translation_group_key,
            'translation_source' => KnowledgeTranslationSource::HumanCommunity,
            'language' => $language,
        ]);
    }
}
