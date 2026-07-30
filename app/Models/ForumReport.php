<?php

namespace App\Models;

use Database\Factories\ForumReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(ForumAnswer::class, 'answer_id');
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(ForumComment::class, 'comment_id');
    }
}
