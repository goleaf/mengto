<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property-read ForumAnswer|null $answer
 * @property int|null $answer_id
 * @property-read ForumComment|null $comment
 * @property int|null $comment_id
 * @property Carbon|null $created_at
 * @property string|null $details
 * @property int $id
 * @property string $priority
 * @property string $reason
 * @property string $reporter_key
 * @property string $status
 * @property-read ForumTopic|null $topic
 * @property int|null $topic_id
 * @property Carbon|null $updated_at
 */
class ForumReport extends Model
{
    /** @use HasFactory<ForumReportFactory> */
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'answer_id',
        'comment_id',
        'subject_type',
        'subject_id',
        'reporter_id',
        'reporter_key',
        'reason',
        'forum_report_reason_id',
        'details',
        'priority',
        'status',
        'affected_user_id',
        'affected_pet_profile_id',
        'duplicate_of_report_id',
        'urgency',
        'location_scope',
        'contact_preference',
        'immediate_safety',
        'truthfulness_confirmed',
        'deduplication_key',
        'idempotency_key',
        'metadata',
    ];

    protected $hidden = [
        'reporter_id',
        'reporter_key',
        'details',
        'location_scope',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'immediate_safety' => 'boolean',
            'truthfulness_confirmed' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /** @return BelongsTo<ForumReportReason, $this> */
    public function reasonDefinition(): BelongsTo
    {
        return $this->belongsTo(ForumReportReason::class, 'forum_report_reason_id');
    }

    /** @return HasMany<ForumReportEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ForumReportEvent::class);
    }

    /** @return BelongsToMany<ForumModerationCase, $this> */
    public function moderationCases(): BelongsToMany
    {
        return $this->belongsToMany(
            ForumModerationCase::class,
            'forum_moderation_case_reports',
        )->withPivot(['linked_by_user_id', 'created_at']);
    }

    /** @return BelongsTo<\App\Models\ForumTopic, $this>*/
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }

    /** @return BelongsTo<\App\Models\ForumAnswer, $this>*/
    public function answer(): BelongsTo
    {
        return $this->belongsTo(ForumAnswer::class, 'answer_id');
    }

    /** @return BelongsTo<\App\Models\ForumComment, $this>*/
    public function comment(): BelongsTo
    {
        return $this->belongsTo(ForumComment::class, 'comment_id');
    }
}
