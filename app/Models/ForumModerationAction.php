<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumModerationActionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ForumModerationAction extends Model
{
    /** @use HasFactory<ForumModerationActionFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_moderation_case_id',
        'forum_moderation_action_definition_id',
        'actor_user_id',
        'target_user_id',
        'target_type',
        'target_id',
        'rule_id',
        'policy_basis',
        'scope_type',
        'scope_key',
        'user_reason_translation_key',
        'internal_reason',
        'evidence',
        'starts_at',
        'ends_at',
        'review_at',
        'appeal_available',
        'reversal_of_action_id',
        'reversed_at',
        'metadata',
    ];

    protected $hidden = ['internal_reason', 'evidence'];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'review_at' => 'immutable_datetime',
            'appeal_available' => 'boolean',
            'reversed_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<ForumModerationCase, $this> */
    public function moderationCase(): BelongsTo
    {
        return $this->belongsTo(ForumModerationCase::class, 'forum_moderation_case_id');
    }

    /** @return BelongsTo<ForumModerationActionDefinition, $this> */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(
            ForumModerationActionDefinition::class,
            'forum_moderation_action_definition_id',
        );
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /** @return BelongsTo<ForumModerationAction, $this> */
    public function reversedAction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_action_id');
    }

    /** @return HasMany<ForumModerationAppeal, $this> */
    public function appeals(): HasMany
    {
        return $this->hasMany(ForumModerationAppeal::class);
    }

    /** @return HasMany<ForumModerationAction, $this> */
    public function reversals(): HasMany
    {
        return $this->hasMany(self::class, 'reversal_of_action_id');
    }

    /** @param Builder<ForumModerationAction> $query @return Builder<ForumModerationAction> */
    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $query
            ->whereNull('reversed_at')
            ->where('starts_at', '<=', now())
            ->where(function (Builder $expiry): void {
                $expiry->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }
}
