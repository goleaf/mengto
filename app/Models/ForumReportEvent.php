<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumReportEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumReportEvent extends Model
{
    /** @use HasFactory<ForumReportEventFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'forum_report_id',
        'actor_user_id',
        'event_type',
        'from_status',
        'to_status',
        'user_message_translation_key',
        'internal_note',
        'metadata',
        'created_at',
    ];

    protected $hidden = ['internal_note'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumReport, $this> */
    public function report(): BelongsTo
    {
        return $this->belongsTo(ForumReport::class, 'forum_report_id');
    }
}
