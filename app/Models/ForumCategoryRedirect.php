<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumCategoryRedirectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumCategoryRedirect extends Model
{
    /** @use HasFactory<ForumCategoryRedirectFactory> */
    use HasFactory;

    protected $fillable = [
        'source_slug',
        'target_forum_category_id',
        'created_by_user_id',
        'reason_code',
        'is_permanent',
    ];

    protected function casts(): array
    {
        return ['is_permanent' => 'boolean'];
    }

    /** @return BelongsTo<ForumCategory, $this> */
    public function target(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'target_forum_category_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
