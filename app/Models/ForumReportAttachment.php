<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumReportAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumReportAttachment extends Model
{
    /** @use HasFactory<ForumReportAttachmentFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_report_id',
        'uploaded_by_user_id',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
        'checksum_sha256',
        'visibility',
    ];

    protected $hidden = ['disk', 'path'];

    protected function casts(): array
    {
        return ['size_bytes' => 'integer'];
    }

    /** @return BelongsTo<ForumReport, $this> */
    public function report(): BelongsTo
    {
        return $this->belongsTo(ForumReport::class, 'forum_report_id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
