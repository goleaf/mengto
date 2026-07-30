<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumVoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read ForumAnswer|null $answer
 * @property int $answer_id
 * @property Carbon|null $created_at
 * @property int $id
 * @property string|null $reason
 * @property Carbon|null $updated_at
 * @property string $user_key
 * @property string $value
 */
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

    /** @return BelongsTo<\App\Models\ForumAnswer, $this>*/
    public function answer(): BelongsTo
    {
        return $this->belongsTo(ForumAnswer::class, 'answer_id');
    }
}
