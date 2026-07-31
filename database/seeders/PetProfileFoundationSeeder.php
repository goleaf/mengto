<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\PetProfileFoundationBackfill;
use Illuminate\Database\Seeder;

final class PetProfileFoundationSeeder extends Seeder
{
    public function run(PetProfileFoundationBackfill $backfill): void
    {
        $backfill->run();
    }
}
