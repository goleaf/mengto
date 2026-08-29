<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumConfirmationEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumConfirmationEvidence extends Model
{
    /** @use HasFactory<ForumConfirmationEvidenceFactory> */
    use HasFactory;

    protected $table = 'forum_confirmation_evidence';

    protected $fillable = [
        'forum_confirmation_id',
        'submitted_by_user_id',
        'evidence_type',
        'summary',
        'source_url',
        'private_disk',
        'private_path',
        'status',
        'metadata',
    ];

    protected $hidden = ['private_disk', 'private_path'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    /** @return BelongsTo<ForumConfirmation, $this> */
    public function confirmation(): BelongsTo
    {
        return $this->belongsTo(ForumConfirmation::class, 'forum_confirmation_id');
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }
}
