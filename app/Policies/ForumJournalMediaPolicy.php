<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ForumJournalMedia;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;

final readonly class ForumJournalMediaPolicy
{
    public function __construct(private Gate $gate) {}

    public function view(?User $user, ForumJournalMedia $media): bool
    {
        $journal = $media->entry()
            ->with('journal.topic')
            ->first()
            ?->journal;

        return $journal !== null
            && $this->gate->forUser($user)->allows('view', $journal);
    }
}
