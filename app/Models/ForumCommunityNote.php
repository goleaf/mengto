<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumCommunityNoteStatus;
use App\Enums\ForumCommunityNoteType;
use Carbon\CarbonImmutable;
use Database\Factories\ForumCommunityNoteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $author_response
 * @property string $body
 * @property int $current_version
 * @property array<int, array<string, string>>|null $evidence
 * @property int $id
 * @property bool $is_safety_notice
 * @property int $lock_version
 * @property ForumCommunityNoteType $note_type
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $revalidation_due_at
 * @property ForumCommunityNoteStatus $status
 * @property int $subject_id
 * @property string $subject_type
 */
final class ForumCommunityNote extends Model
{
    /** @use HasFactory<ForumCommunityNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'proposer_user_id',
        'subject_author_user_id',
        'note_type',
        'status',
        'body',
        'evidence',
        'author_response',
        'jurisdiction',
        'species_context',
        'is_safety_notice',
        'forum_review_panel_id',
        'moderator_user_id',
        'moderator_decision',
        'decision_reason',
        'current_version',
        'lock_version',
        'published_at',
        'revalidation_due_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'note_type' => ForumCommunityNoteType::class,
            'status' => ForumCommunityNoteStatus::class,
            'evidence' => 'array',
            'is_safety_notice' => 'boolean',
            'current_version' => 'integer',
            'lock_version' => 'integer',
            'published_at' => 'immutable_datetime',
            'revalidation_due_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
        ];
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ForumCommunityNoteStatus::Published->value,
            ForumCommunityNoteStatus::Revised->value,
            ForumCommunityNoteStatus::RevalidationDue->value,
        ]);
    }

    /** @return BelongsTo<User, $this> */
    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposer_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function subjectAuthor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_author_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_user_id');
    }

    /** @return BelongsTo<ForumReviewPanel, $this> */
    public function reviewPanel(): BelongsTo
    {
        return $this->belongsTo(ForumReviewPanel::class, 'forum_review_panel_id');
    }

    /** @return HasMany<ForumCommunityNoteVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(ForumCommunityNoteVersion::class);
    }
}
