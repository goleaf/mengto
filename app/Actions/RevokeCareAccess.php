<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\CareAccessGrant;
use App\Models\CareJournal;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RevokeCareAccess
{
    public function __construct(private readonly ForumActor $actor) {}

    public function handle(CareJournal $journal, CareAccessGrant $grant): void
    {
        DB::transaction(function () use ($journal, $grant): void {
            if ($grant->care_journal_id !== $journal->id) {
                throw ValidationException::withMessages([
                    'grant' => 'This access grant does not belong to the selected journal.',
                ]);
            }

            $grant->forceFill(['revoked_at' => now()])->save();

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'care-journal-owner',
                'action' => 'care-access.revoked',
                'target_type' => CareAccessGrant::class,
                'target_id' => (string) $grant->id,
                'metadata' => ['care_journal_id' => $journal->id],
            ]);
        });
    }
}
