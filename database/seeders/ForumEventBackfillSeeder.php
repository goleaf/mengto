<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\BackfillForumEvents;
use Illuminate\Database\Seeder;

final class ForumEventBackfillSeeder extends Seeder
{
    public function run(BackfillForumEvents $backfill): void
    {
        $backfill->handle();
    }
}
