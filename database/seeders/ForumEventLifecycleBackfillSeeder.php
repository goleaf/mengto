<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\BackfillForumEventLifecycle;
use Illuminate\Database\Seeder;

final class ForumEventLifecycleBackfillSeeder extends Seeder
{
    public function run(): void
    {
        app(BackfillForumEventLifecycle::class)->handle();
    }
}
