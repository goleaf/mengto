<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PetProfileFoundationBackfill;
use Illuminate\Console\Command;

final class BackfillPetProfileFoundation extends Command
{
    protected $signature = 'pets:backfill-profile-foundation {--chunk=500 : Profiles per bounded read chunk}';

    protected $description = 'Idempotently backfill pet managers, privacy settings, aliases, and audit events.';

    public function handle(PetProfileFoundationBackfill $backfill): int
    {
        $chunkSize = max(1, min(2000, (int) $this->option('chunk')));
        $counts = $backfill->run($chunkSize);

        $this->components->info(sprintf(
            'Processed %d pets; created %d managers, %d privacy settings, and %d aliases; normalized %d profiles.',
            $counts['processed'],
            $counts['managers'],
            $counts['privacy'],
            $counts['aliases'],
            $counts['profiles_normalized'],
        ));

        return self::SUCCESS;
    }
}
