<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\MedicalAccessGrant;
use App\Models\MedicalRecord;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RevokeMedicalAccess
{
    public function __construct(private readonly ForumActor $actor) {}

    public function handle(MedicalRecord $record, MedicalAccessGrant $grant): void
    {
        DB::transaction(function () use ($record, $grant): void {
            if ($grant->medical_record_id !== $record->id) {
                throw ValidationException::withMessages([
                    'grant' => __('messages.this_access_grant_does_not_belong_to_the_selected_record_dd280436cc'),
                ]);
            }

            $grant->forceFill(['revoked_at' => now()])->save();

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'medical-record-owner',
                'action' => 'medical-access.revoked',
                'target_type' => MedicalAccessGrant::class,
                'target_id' => (string) $grant->id,
                'metadata' => ['medical_record_id' => $record->id],
            ]);
        });
    }
}
