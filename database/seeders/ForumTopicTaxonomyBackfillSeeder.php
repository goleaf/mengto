<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\BackfillForumTopicTaxonomy;
use Illuminate\Database\Seeder;

final class ForumTopicTaxonomyBackfillSeeder extends Seeder
{
    public function run(BackfillForumTopicTaxonomy $backfill): void
    {
        $backfill->handle();
    }
}
