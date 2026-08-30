<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceManagementReviewerRole;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceManagementReviewerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable $appointed_at
 * @property int|null $appointed_by_user_id
 * @property CarbonImmutable|null $expires_at
 * @property int $id
 * @property bool $is_active
 * @property CarbonImmutable|null $revoked_at
 * @property PlaceManagementReviewerRole $role
 * @property int $user_id
 */
final class PlaceManagementReviewer extends Model
{
    /** @use HasFactory<PlaceManagementReviewerFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'appointed_by_user_id',
        'role',
        'is_active',
        'appointed_at',
        'expires_at',
        'revoked_at',
    ];

    protected $attributes = ['is_active' => true];

    protected function casts(): array
    {
        return [
            'role' => PlaceManagementReviewerRole::class,
            'is_active' => 'boolean',
            'appointed_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function appointedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appointed_by_user_id');
    }

    /** @param Builder<PlaceManagementReviewer> $query */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNull('revoked_at')
            ->where(function (Builder $expiry): void {
                $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function isCurrent(): bool
    {
        return $this->is_active
            && $this->revoked_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
