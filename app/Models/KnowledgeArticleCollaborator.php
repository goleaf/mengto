<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KnowledgeCollaboratorRole;
use Database\Factories\KnowledgeArticleCollaboratorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $article_id
 * @property string|null $attribution_name
 * @property Carbon|null $created_at
 * @property int $id
 * @property KnowledgeCollaboratorRole $role
 * @property Carbon|null $revoked_at
 * @property int $user_id
 * @property-read KnowledgeArticle|null $article
 * @property-read User|null $user
 */
final class KnowledgeArticleCollaborator extends Model
{
    /** @use HasFactory<KnowledgeArticleCollaboratorFactory> */
    use HasFactory;

    protected $fillable = [
        'article_id',
        'user_id',
        'role',
        'added_by_user_id',
        'attribution_name',
        'revoked_at',
        'revoked_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'role' => KnowledgeCollaboratorRole::class,
            'revoked_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<KnowledgeArticle, $this> */
    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'article_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }
}
