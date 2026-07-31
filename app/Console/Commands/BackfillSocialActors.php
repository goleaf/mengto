<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SocialActorFoundationBackfill;
use Illuminate\Console\Command;

final class BackfillSocialActors extends Command
{
    protected $signature = 'social:backfill-actors {--dry-run : Report without creating adapters} {--chunk=200 : Batch size}';

    protected $description = 'Create canonical social actor adapters and report legacy prototype state';

    public function handle(SocialActorFoundationBackfill $backfill): int
    {
        $chunkSize = max(1, min(1000, (int) $this->option('chunk')));
        $dryRun = (bool) $this->option('dry-run');
        $counts = $backfill->run($dryRun, $chunkSize);

        $this->table(
            [__('social_relationships.backfill.type'), __('social_relationships.backfill.count')],
            collect($counts)->map(
                static fn (int $count, string $type): array => [$type, $count],
            )->values()->all(),
        );
        $this->components->info($dryRun
            ? __('social_relationships.backfill.dry_run_complete')
            : __('social_relationships.backfill.complete'));
        $this->components->warn(__('social_relationships.backfill.legacy_retained'));

        return self::SUCCESS;
    }
}
