<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'reporter_key',
        'reason',
        'details',
        'priority',
        'status',
    ];

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
