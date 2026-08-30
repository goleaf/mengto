<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

final readonly class ResolveAccessiblePlace
{
    public function handle(User $actor, string $identifier): ?Place
    {
        return Place::query()
            ->select([
                'id',
                'owner_user_id',
                'organization_id',
                'stable_key',
                'slug',
                'name',
                'visibility',
                'status',
                'archived_at',
            ])
            ->with([
                'organization:id,status,archived_at',
                'organization.memberships' => static function (Relation $memberships) use ($actor): void {
                    $memberships
                        ->select([
                            'id',
                            'organization_id',
                            'user_id',
                            'role',
                            'status',
                            'expires_at',
                            'removed_at',
                        ])
                        ->where('user_id', $actor->id);
                },
            ])
            ->accessibleTo($actor)
            ->where(function (Builder $identifiers) use ($identifier): void {
                $identifiers
                    ->where('stable_key', $identifier)
                    ->orWhere('slug', $identifier);
            })
            ->first();
    }
}
