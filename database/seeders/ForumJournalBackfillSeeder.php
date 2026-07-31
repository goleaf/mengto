<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\BackfillForumJournals;
use Illuminate\Database\Seeder;

final class ForumJournalBackfillSeeder extends Seeder
{
    public function run(BackfillForumJournals $backfill): void
    {
        $backfill->handle();
    }
}
