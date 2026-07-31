<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumTopicStatus;
use App\Enums\ForumTopicType;
use App\Enums\ForumVisibility;
use App\Models\ForumGroup;
use App\Models\ForumTopic;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumTopic>
 */
class ForumTopicFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(8);

        return [
            'author_key' => fake()->unique()->userName(),
            'author_name' => fake()->name(),
            'author_initials' => fake()->lexify('??'),
            'author_role' => 'Pet owner',
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'type' => ForumTopicType::Question,
            'title' => $title,
            'body' => fake()->paragraphs(3, true),
            'category' => fake()->randomElement(['health', 'behavior', 'travel', 'care']),
            'subcategory' => null,
            'tags' => [fake()->word(), fake()->word()],
            'pet_key' => 'scout',
            'pet_name' => 'Scout',
            'pet_species' => 'Dog',
            'pet_age_label' => '4 years',
            'location' => 'Vilnius',
            'status' => ForumTopicStatus::Published,
            'visibility' => ForumVisibility::Public,
            'desired_answer' => 'Personal experience and expert guidance',
            'comment_policy' => 'registered',
            'language' => 'en',
            'media' => [],
            'is_urgent' => false,
            'is_medical' => false,
            'is_locked' => false,
            'has_expert_answer' => false,
            'view_count' => fake()->numberBetween(12, 900),
            'last_activity_at' => now(),
            'published_at' => now(),
            'state_entered_at' => now(),
            'last_author_update_at' => now(),
            'retention_until' => now()->addYears(7),
            'structured_data_version' => 1,
            'lock_version' => 1,
        ];
    }

    public function medical(): static
    {
        return $this->state(fn (): array => [
            'category' => 'health',
            'is_medical' => true,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumTopicStatus::Solved,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumTopicStatus::Draft,
            'visibility' => ForumVisibility::Private,
            'published_at' => null,
        ]);
    }

    public function outdated(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumTopicStatus::Outdated,
            'outdated_at' => now(),
            'state_entered_at' => now(),
            'last_activity_at' => now()->subYear(),
            'last_author_update_at' => now()->subYear(),
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumTopicStatus::Locked,
            'is_locked' => true,
            'locked_at' => now(),
            'state_entered_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumTopicStatus::Archived,
            'archived_at' => now(),
            'state_entered_at' => now(),
        ]);
    }

    public function removed(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumTopicStatus::Removed,
            'removed_at' => now(),
            'state_entered_at' => now(),
        ]);
    }

    public function restored(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumTopicStatus::Restored,
            'restored_at' => now(),
            'state_entered_at' => now(),
        ]);
    }

    public function forGroup(?ForumGroup $group = null): static
    {
        return $this->state(fn (): array => [
            'forum_group_id' => $group === null ? ForumGroup::factory() : $group->id,
            'visibility' => ForumVisibility::Group,
        ]);
    }
}
