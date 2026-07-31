<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumCategoryAliasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumCategoryAlias extends Model
{
    /** @use HasFactory<ForumCategoryAliasFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_category_id',
        'locale',
        'alias',
        'normalized_alias',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return BelongsTo<ForumCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }
}
