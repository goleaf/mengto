<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PlaceCompatibilityBackfillService;
use Illuminate\Console\Command;

final class BackfillPlaceContributions extends Command
{
    protected $signature = 'places:backfill-contributions
        {--dry-run : Report compatibility rows without writing canonical records}
        {--chunk=100 : Number of encrypted state rows processed per batch}';

    protected $description = 'Import preserved places.state.v1 contributions into normalized workflows';

    public function handle(PlaceCompatibilityBackfillService $backfill): int
    {
        $result = $backfill->handle(
            dryRun: (bool) $this->option('dry-run'),
            chunkSize: max(1, min(500, (int) $this->option('chunk'))),
        );

        $this->line(json_encode($result, JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}
