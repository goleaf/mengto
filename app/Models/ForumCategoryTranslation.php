<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumCategoryTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumCategoryTranslation extends Model
{
    /** @use HasFactory<ForumCategoryTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_category_id',
        'locale',
        'name',
        'description',
        'notice',
        'rules_summary',
        'is_reviewed',
    ];

    protected function casts(): array
    {
        return ['is_reviewed' => 'boolean'];
    }

    /** @return BelongsTo<ForumCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }
}
