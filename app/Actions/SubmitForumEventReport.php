<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumEvent;
use App\Models\ForumReport;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;

final readonly class SubmitForumEventReport
{
    public function __construct(
        private Gate $gate,
        private SubmitForumReport $reports,
    ) {}

    public function handle(
        User $actor,
        ForumEvent $event,
        string $reasonKey,
        ?string $details,
        bool $truthfulnessConfirmed,
        bool $immediateSafety,
    ): ForumReport {
        $this->gate->forUser($actor)->authorize('report', $event);

        return $this->reports->handle(
            reporter: $actor,
            subject: $event,
            reasonKey: $reasonKey,
            details: $details,
            truthfulnessConfirmed: $truthfulnessConfirmed,
            immediateSafety: $immediateSafety,
            metadata: ['event_key' => $event->stable_key],
        );
    }
}
