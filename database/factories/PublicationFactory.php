<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\ExpertProfile;
use App\Models\Publication;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<Publication> */
class PublicationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(7);

        return [
            'expert_profile_id' => ExpertProfile::factory(),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(10, 999),
            'title' => $title,
            'summary' => fake()->paragraph(),
            'body' => fake()->paragraphs(5, true),
            'type' => 'guide',
            'category' => 'care',
            'tags' => ['care', 'professional-guide'],
            'sources' => ['https://example.org/professional-guidance'],
            'conflict_disclosure' => 'No commercial relationship.',
            'language' => 'en',
            'status' => PublicationStatus::Published,
            'last_reviewed_at' => now()->subMonth(),
            'published_at' => now()->subMonths(2),
        ];
    }
}
