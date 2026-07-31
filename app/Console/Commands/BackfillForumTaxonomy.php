<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\BackfillForumTopicTaxonomy;
use Illuminate\Console\Command;

final class BackfillForumTaxonomy extends Command
{
    protected $signature = 'forum:backfill-taxonomy';

    protected $description = 'Attach legacy forum topics to normalized categories and topic types';

    public function handle(BackfillForumTopicTaxonomy $backfill): int
    {
        $result = $backfill->handle();

        $this->components->info(sprintf(
            'Assigned %d categories and %d topic types.',
            $result->categoryAssignments,
            $result->topicTypeAssignments,
        ));

        if ($result->unmappedCategories > 0 || $result->unmappedTopicTypes > 0) {
            $this->components->warn(sprintf(
                '%d category values and %d topic type values require review.',
                $result->unmappedCategories,
                $result->unmappedTopicTypes,
            ));
        }

        return self::SUCCESS;
    }
}
