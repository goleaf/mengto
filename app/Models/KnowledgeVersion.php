<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KnowledgeStatus;
use Database\Factories\KnowledgeVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * @property-read KnowledgeArticle|null $article
 * @property int $article_id
 * @property string $body
 * @property string $change_summary
 * @property Carbon|null $created_at
 * @property string $edited_by
 * @property int|null $editor_user_id
 * @property int $id
 * @property string|null $jurisdiction
 * @property string|null $language
 * @property array<array-key, mixed>|null $protected_sections
 * @property array<array-key, mixed>|null $sources
 * @property KnowledgeStatus|null $status
 * @property string|null $summary
 * @property int|null $taxon_id
 * @property string $title
 * @property Carbon|null $updated_at
 * @property int $version_number
 * @property-read User|null $editor
 * @property-read Taxon|null $taxon
 */
class KnowledgeVersion extends Model
{
    /** @use HasFactory<KnowledgeVersionFactory> */
    use HasFactory;

    protected $fillable = [
        'article_id',
        'version_number',
        'title',
        'body',
        'edited_by',
        'editor_user_id',
        'change_summary',
        'status',
        'summary',
        'sources',
        'language',
        'jurisdiction',
        'taxon_id',
        'protected_sections',
    ];

    protected function casts(): array
    {
        return [
            'status' => KnowledgeStatus::class,
            'sources' => 'array',
            'protected_sections' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('Knowledge version snapshots are append-only.');
        });
        static::deleting(static function (): never {
            throw new LogicException('Knowledge version snapshots are append-only.');
        });
    }

    /** @return BelongsTo<\App\Models\KnowledgeArticle, $this>*/
    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'article_id');
    }

    /** @return BelongsTo<User, $this> */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_user_id');
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }
}
