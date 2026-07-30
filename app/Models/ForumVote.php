<?php

namespace App\Models;

use Database\Factories\ForumVoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumVote extends Model
{
    /** @use HasFactory<ForumVoteFactory> */
    use HasFactory;

    protected $fillable = [
        'answer_id',
        'user_key',
        'value',
        'reason',
    ];

    public function answer(): BelongsTo
    {
        return $this->belongsTo(ForumAnswer::class, 'answer_id');
    }
}
