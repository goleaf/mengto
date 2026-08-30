<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumAnswer;
use App\Models\ForumTopic;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumAnswer>
 */
class ForumAnswerFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic_id' => ForumTopic::factory(),
            'author_id' => null,
            'author_key' => fake()->unique()->userName(),
            'author_name' => fake()->name(),
            'author_initials' => fake()->lexify('??'),
            'author_role' => 'Experienced pet owner',
            'body' => fake()->paragraphs(2, true),
            'experience_type' => 'personal-experience',
            'is_verified_expert' => false,
            'sources' => [],
            'media' => [[
                'type' => 'image',
                'url' => asset('images/places/community-primary-lg.jpg'),
                'alt' => 'A pet community meeting place',
            ]],
            'status' => 'published',
            'is_accepted' => false,
            'is_highlighted' => false,
            'needs_source' => false,
            'helpful_count' => 0,
        ];
    }

    public function expert(): static
    {
        return $this->state(fn (): array => [
            'author_role' => 'Verified veterinarian',
            'experience_type' => 'professional-opinion',
            'is_verified_expert' => true,
            'expertise' => 'Companion animal medicine',
            'qualification_region' => 'Lithuania',
        ]);
    }

    public function configure(): static
    {
        return $this->afterMaking(static function (ForumAnswer $answer): void {
            if ($answer->author_id === null) {
                return;
            }

            $author = User::query()->findOrFail($answer->author_id);
            $answer->author_key = $author->actor_key;
            $answer->author_name = $author->name;
            $answer->author_initials = collect(preg_split('/\s+/', trim($author->name)) ?: [])
                ->filter()
                ->take(2)
                ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('');
        });
    }
}
