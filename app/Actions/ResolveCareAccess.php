<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\CareAccessGrant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ResolveCareAccess
{
    public function handle(
        string $token,
        string $action = 'care-access.opened',
        bool $countView = true,
    ): CareAccessGrant {
        return DB::transaction(function () use ($token, $action, $countView): CareAccessGrant {
            $grant = CareAccessGrant::query()
                ->select([
                    'id', 'care_journal_id', 'granted_by_key', 'recipient_key',
                    'recipient_name', 'recipient_role', 'label', 'token_hash',
                    'sections', 'permissions', 'allow_add', 'allow_location',
                    'allow_media', 'max_views', 'views_used', 'expires_at',
                    'last_opened_at', 'revoked_at', 'created_at', 'updated_at',
                ])
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if ($grant === null || ! $grant->canBeOpened()) {
                throw (new ModelNotFoundException)->setModel(CareAccessGrant::class);
            }

            if ($countView) {
                $grant->forceFill([
                    'views_used' => $grant->views_used + 1,
                    'last_opened_at' => now(),
                ])->save();
            }

            AuditLog::query()->create([
                'actor_key' => $grant->recipient_key ?? 'temporary-link',
                'actor_role' => $grant->recipient_role,
                'action' => $action,
                'target_type' => CareAccessGrant::class,
                'target_id' => (string) $grant->id,
                'metadata' => [
                    'care_journal_id' => $grant->care_journal_id,
                    'sections' => $grant->sections,
                    'views_used' => $grant->views_used,
                    'max_views' => $grant->max_views,
                ],
            ]);

            return $grant->load([
                'careJournal' => fn ($journals) => $journals->select([
                    'id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
                    'species', 'breed', 'image_url', 'privacy', 'timezone',
                    'status', 'last_feeding_at', 'last_water_at',
                    'last_walk_at', 'last_toilet_at', 'updated_at',
                ]),
            ]);
        });
    }
}
