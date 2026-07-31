<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ContentInteractionSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentInteractionSetting extends Model
{
    /** @use HasFactory<ContentInteractionSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'content_publication_id',
        'allow_comments',
        'allow_reactions',
        'allow_reposts',
        'allow_external_sharing',
        'allow_media_downloads',
        'allow_mentions',
        'is_searchable',
        'allow_external_indexing',
        'show_reaction_counts',
    ];

    protected function casts(): array
    {
        return [
            'allow_comments' => 'boolean',
            'allow_reactions' => 'boolean',
            'allow_reposts' => 'boolean',
            'allow_external_sharing' => 'boolean',
            'allow_media_downloads' => 'boolean',
            'allow_mentions' => 'boolean',
            'is_searchable' => 'boolean',
            'allow_external_indexing' => 'boolean',
            'show_reaction_counts' => 'boolean',
        ];
    }

    /** @return BelongsTo<ContentPublication, $this> */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(ContentPublication::class, 'content_publication_id');
    }
}
