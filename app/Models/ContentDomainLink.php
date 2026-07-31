<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentDomainType;
use Database\Factories\ContentDomainLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $content_publication_id
 * @property string $domain_key
 * @property ContentDomainType $domain_type
 * @property int $id
 * @property bool $is_primary
 * @property string $relationship
 * @property-read ContentPublication $publication
 */
final class ContentDomainLink extends Model
{
    /** @use HasFactory<ContentDomainLinkFactory> */
    use HasFactory;

    protected $fillable = [
        'content_publication_id',
        'domain_type',
        'domain_key',
        'relationship',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'domain_type' => ContentDomainType::class,
            'is_primary' => 'boolean',
        ];
    }

    /** @return BelongsTo<ContentPublication, $this> */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(ContentPublication::class, 'content_publication_id');
    }
}
