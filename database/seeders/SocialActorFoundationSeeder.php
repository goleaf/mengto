<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\SocialActorFoundationBackfill;
use Illuminate\Database\Seeder;

final class SocialActorFoundationSeeder extends Seeder
{
    public function run(SocialActorFoundationBackfill $backfill): void
    {
        $backfill->run();
    }
}
