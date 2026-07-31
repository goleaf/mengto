<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumConfirmationVoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumConfirmationVote extends Model
{
    /** @use HasFactory<ForumConfirmationVoteFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_confirmation_id',
        'voter_user_id',
        'stance',
        'weight',
        'has_conflict',
        'conflict_type',
        'independence_cluster',
        'reasoning',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'has_conflict' => 'boolean',
        ];
    }

    /** @return BelongsTo<ForumConfirmation, $this> */
    public function confirmation(): BelongsTo
    {
        return $this->belongsTo(ForumConfirmation::class);
    }

    /** @return BelongsTo<User, $this> */
    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_user_id');
    }
}
