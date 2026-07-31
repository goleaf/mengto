<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class ForumSystemSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ForumCategorySeeder::class,
            ForumTopicTypeSeeder::class,
            ForumReputationDefinitionSeeder::class,
            ForumModerationDefinitionSeeder::class,
            ForumTopicLifecycleBackfillSeeder::class,
            ForumTopicTaxonomyBackfillSeeder::class,
            CatalogueOfLifeSourceSeeder::class,
            CoreAnimalTaxonomySeeder::class,
            CommunityAnimalGroupSeeder::class,
            ForumGroupDefinitionSeeder::class,
            ForumJournalBackfillSeeder::class,
            ForumEventBackfillSeeder::class,
            ForumExpertSessionBackfillSeeder::class,
        ]);
    }
}
