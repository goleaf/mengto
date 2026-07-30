<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\CareAccessGrant;
use App\Models\CareJournal;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateCareAccessGrant
{
    public function __construct(private readonly ForumActor $actor) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{grant: CareAccessGrant, token: string}
     */
    public function handle(CareJournal $journal, array $data): array
    {
        return DB::transaction(function () use ($journal, $data): array {
            $token = Str::random(64);
            $allowAdd = (bool) ($data['allow_add'] ?? false);
            $grant = $journal->accessGrants()->create([
                'granted_by_key' => $this->actor->key(),
                'recipient_key' => $data['recipient_key'] ?? null,
                'recipient_name' => $data['recipient_name'],
                'recipient_role' => $data['recipient_role'],
                'label' => $data['label'],
                'token_hash' => hash('sha256', $token),
                'sections' => array_values($data['sections']),
                'permissions' => $allowAdd ? ['view', 'add'] : ['view'],
                'allow_add' => $allowAdd,
                'allow_location' => (bool) ($data['allow_location'] ?? false),
                'allow_media' => (bool) ($data['allow_media'] ?? false),
                'max_views' => $data['max_views'],
                'views_used' => 0,
                'expires_at' => now()->addHours((int) $data['expires_in_hours']),
            ]);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'care-journal-owner',
                'action' => 'care-access.created',
                'target_type' => CareAccessGrant::class,
                'target_id' => (string) $grant->id,
                'metadata' => [
                    'care_journal_id' => $journal->id,
                    'recipient_role' => $grant->recipient_role,
                    'sections' => $grant->sections,
                    'allow_add' => $grant->allow_add,
                    'allow_location' => $grant->allow_location,
                    'allow_media' => $grant->allow_media,
                    'expires_at' => $grant->expires_at?->toAtomString(),
                ],
            ]);

            return ['grant' => $grant, 'token' => $token];
        });
    }
}
