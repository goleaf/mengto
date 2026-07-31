<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumModerationCaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ForumModerationCase extends Model
{
    /** @use HasFactory<ForumModerationCaseFactory> */
    use HasFactory;

    protected $fillable = [
        'case_number',
        'status',
        'priority',
        'assigned_to_user_id',
        'opened_by_user_id',
        'subject_type',
        'subject_id',
        'summary_translation_key',
        'internal_summary',
        'review_due_at',
        'resolved_at',
        'closed_at',
        'retention_until',
        'metadata',
    ];

    protected $hidden = ['internal_summary'];

    protected function casts(): array
    {
        return [
            'review_due_at' => 'immutable_datetime',
            'resolved_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'retention_until' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    /** @return BelongsToMany<ForumReport, $this> */
    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(
            ForumReport::class,
            'forum_moderation_case_reports',
        )->withPivot(['linked_by_user_id', 'created_at']);
    }

    /** @return HasMany<ForumModerationAction, $this> */
    public function actions(): HasMany
    {
        return $this->hasMany(ForumModerationAction::class);
    }

    /** @return HasMany<ForumModeratorRecusal, $this> */
    public function recusals(): HasMany
    {
        return $this->hasMany(ForumModeratorRecusal::class);
    }
}
