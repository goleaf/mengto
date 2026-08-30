<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\ActivatePendingEmailUsers as ActivatePendingEmailUsersAction;
use Illuminate\Console\Command;
use LogicException;

final class ActivatePendingEmailUsers extends Command
{
    protected $signature = 'auth:activate-pending-email-users
        {--dry-run : Report eligible accounts without changing data}
        {--chunk=200 : Accounts processed per transaction}';

    protected $description = 'Activate active accounts waiting for email verification when verification is disabled';

    public function handle(ActivatePendingEmailUsersAction $action): int
    {
        $chunkSize = max(1, min(1000, (int) $this->option('chunk')));

        try {
            $result = $action->handle((bool) $this->option('dry-run'), $chunkSize);
        } catch (LogicException) {
            $this->components->error(__('auth.email_verification_activation.enabled_error'));

            return self::FAILURE;
        }

        $key = $this->option('dry-run')
            ? 'auth.email_verification_activation.dry_run'
            : 'auth.email_verification_activation.complete';

        $this->components->info(__($key, $result));

        return self::SUCCESS;
    }
}
