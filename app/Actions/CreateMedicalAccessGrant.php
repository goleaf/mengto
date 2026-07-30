<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\MedicalAccessGrant;
use App\Models\MedicalRecord;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateMedicalAccessGrant
{
    public function __construct(private readonly ForumActor $actor) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{grant: MedicalAccessGrant, token: string}
     */
    public function handle(MedicalRecord $record, array $data): array
    {
        return DB::transaction(function () use ($record, $data): array {
            $token = Str::random(64);
            $grant = $record->accessGrants()->create([
                'granted_by_key' => $this->actor->key(),
                'recipient_key' => $data['recipient_key'] ?? null,
                'recipient_name' => $data['recipient_name'],
                'recipient_role' => $data['recipient_role'],
                'label' => $data['label'],
                'token_hash' => hash('sha256', $token),
                'sections' => array_values($data['sections']),
                'permissions' => $data['allow_edit'] ?? false
                    ? ['view', 'add']
                    : ['view'],
                'allow_download' => (bool) ($data['allow_download'] ?? false),
                'allow_edit' => (bool) ($data['allow_edit'] ?? false),
                'max_views' => $data['max_views'],
                'views_used' => 0,
                'expires_at' => now()->addHours((int) $data['expires_in_hours']),
            ]);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'medical-record-owner',
                'action' => 'medical-access.created',
                'target_type' => MedicalAccessGrant::class,
                'target_id' => (string) $grant->id,
                'metadata' => [
                    'medical_record_id' => $record->id,
                    'recipient_role' => $grant->recipient_role,
                    'sections' => $grant->sections,
                    'expires_at' => $grant->expires_at?->toAtomString(),
                    'allow_download' => $grant->allow_download,
                ],
            ]);

            return ['grant' => $grant, 'token' => $token];
        });
    }
}
