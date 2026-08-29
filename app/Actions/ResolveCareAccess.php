<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\CareAccessGrant;
use App\Services\ForumActor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ResolveCareAccess
{
    public function __construct(private readonly ForumActor $actor) {}

    public function handle(
        string $token,
        string $action = 'care-access.opened',
        bool $countView = true,
        bool $recordAudit = true,
    ): CareAccessGrant {
        return $this->execute(
            $token,
            $action,
            static fn (CareAccessGrant $grant): CareAccessGrant => $grant,
            $countView,
            $recordAudit,
        );
    }

    /**
     * @template TResult
     *
     * @param  callable(CareAccessGrant): TResult  $operation
     * @return TResult
     */
    public function execute(
        string $token,
        string $action,
        callable $operation,
        bool $countView = true,
        bool $recordAudit = true,
    ): mixed {
        return DB::transaction(function () use (
            $token,
            $action,
            $operation,
            $countView,
            $recordAudit,
        ): mixed {
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

            $actorKey = $this->actor->requireUser()->actor_key;

            if ($grant->recipient_key !== null && ! hash_equals($grant->recipient_key, $actorKey)) {
                throw (new ModelNotFoundException)->setModel(CareAccessGrant::class);
            }

            $grant->load([
                'careJournal' => fn ($journals) => $journals->select([
                    'id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
                    'species', 'breed', 'image_url', 'privacy', 'timezone',
                    'current_caregiver_name', 'status', 'last_feeding_at', 'last_water_at',
                    'last_walk_at', 'last_toilet_at', 'updated_at',
                ]),
            ]);

            $result = $operation($grant);

            if ($countView) {
                $grant->forceFill([
                    'views_used' => $grant->views_used + 1,
                    'last_opened_at' => now(),
                ])->save();
            }

            if ($recordAudit) {
                AuditLog::query()->create([
                    'actor_key' => $actorKey,
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
            }

            return $result;
        });
    }
}
