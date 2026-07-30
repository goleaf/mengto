<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $blocked_author_key
 * @property Carbon|null $created_at
 * @property int $id
 * @property string|null $reason
 * @property Carbon|null $updated_at
 * @property string $user_key
 */
class ForumBlock extends Model
{
    /** @use HasFactory<ForumBlockFactory> */
    use HasFactory;

    protected $fillable = [
        'user_key',
        'blocked_author_key',
        'reason',
    ];
}
